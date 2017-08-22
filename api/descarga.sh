#!/bin/bash


##
# Cores default que serão utilizadas
RED='\033[1;31m'
ABORT='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m'

##
# Variáveis de escopo global
target_default=""
ask=1
path="0"
server_url="http://localhost/appsindicato/api"
deviceid="0"
globalmd5="0"
invalidskey="0"
urnaid="0"

##
# Requisita as configurações do servidor remoto
# 
get_config(){
	remote_configs=$(curl "$server_url/configure")
	if [ $? == 0 ]; then
		globalmd5=$(echo $remote_configs | jq -r '.md5')
		if [ $? != 0 ]; then
			echo -e "${ABORT}[FAIL] Erro ao obter configurações globais do sistema!!! ABORTANDO${NC}"
			exit
		fi
		deviceid=$(echo $remote_configs | jq -r '.device_id')
		if [ $? != 0 ]; then
			echo -e "${ABORT}[FAIL] Erro ao obter configurações globais do sistema!!! ABORTANDO${NC}"
			exit
		fi
	else
		echo -e "${ABORT}[FAIL] Erro ao obter configurações globais do sistema!!! ABORTANDO${NC}"
		exit
	fi
	
}

##
# Verifica se o sistema de arquivos destino é válido ou não. Utiliza um md5 do sistema de arquivos para validar,
# uma busca via API é realizada para saber se o md5 do sistema de arquivo local é igual ao que o sistema possui.
# 
# @param $1 Caminho do sistema que será validado
# @param $2 Define se irá tentar criar o sistema de arquivo ou não, em caso de erro
# 
# @return int 0 quando sistema inválido | 1 quando sistema válido
validate_system(){
	md5=$(find $1 -name "*.php" -type f -exec md5sum {} \;  | sort -k 2 | cut -d" " -f1 | md5sum | cut -d" " -f1)
	if [ "$md5" = "$globalmd5" ]; then
		echo -e "${GREEN}[OK] Sistema de arquivos válido${NC}"
		return 1
	else
		echo -e "${RED}[ERROR] Sistema de arquivos inválido em $1 ${NC}"
		return 0
	fi
}


##
# Verifica dependencias do sistema e aborta caso alguma dependencia não seja encontrada
# 
# @param $1 Dependencia que irá buscar
verify_dependencies(){
	COMMAND=$1

	if hash $COMMAND 2>/dev/null; then
		echo -e "${GREEN}[OK] $COMMAND ${NC}"
	else
		echo -e "${ABORT}[FAIL] $COMMAND não encontrado: Tente 'apt-get install $COMMAND' ${NC}"
		exit
	fi

}

send_files(){
	if [ -f "$1/var/www/html/urna/files/result.vt" ]; then
		file1="$1/var/www/html/urna/files/result.vt"
	else
		echo -e "${ABORT}[FAIL] Arquivo de ELEITORES não encontrado ${NC}"
		exit
	fi
	if [ -f "$1/var/www/html/urna/files/votes.vt" ]; then
		file2="$1/var/www/html/urna/files/votes.vt"
	else
		echo -e "${ABORT}[FAIL] Arquivo de VOTOS não encontrado ${NC}"
		exit
	fi
	curl -o "urna_$urnaid.pdf" -X POST $server_url/upload/vote -H 'cache-control: no-cache' -H 'content-type: multipart/form-data;' -F result=@$file1 -F vote=@$file2 -F password_1=$2 -F password_2=$3 
	if [ $? == 0 ]; then
		echo -e "${GREEN}[OK] Arquivos descarregados com sucesso! ${NC}"
	fi
}

get_id(){
	urnaid=$(cat $1/var/www/html/urna/config.ini | jq -r '.urna')
}

echo "Iniciando descarga de dados"
echo ''
echo 'Verificando dependencias:'
verify_dependencies git
verify_dependencies mount
verify_dependencies find
verify_dependencies rsync
verify_dependencies md5sum
verify_dependencies jq
verify_dependencies curl
verify_dependencies blkid
verify_dependencies dialog
get_config
device=$deviceid
exec 3>&1;
pwd1=$(dialog --inputbox "Digite a senha 1:" 10 20 2>&1 1>&3);

exec 3>&1;
pwd2=$(dialog --inputbox "Digite a senha 2:" 10 20 2>&1 1>&3);
clear
clone="y"
while [ $clone = "y" ]; do
	target=$(mount | grep '^/dev' | grep $deviceid | cut -d" " -f3)
	if [ -z $target ]; then
		echo -e "${RED}[ERROR] Não foi possível encontrar dispositivo identificado por $deviceid ${NC}"
		echo "Tentando montar dispositivo..."
		#sdevice=$(blkid | grep "85ddb6c6-a6fd-4ad2-a75c-332d6cf35eb6" | cut -d" " -f1 | cut -d":" -f1)
		#mkdir /mnt/85ddb6c6-a6fd-4ad2-a75c-332d6cf35eb6
		#mount $sdevice /mnt/85ddb6c6-a6fd-4ad2-a75c-332d6cf35eb6
		sdevice=$(blkid | grep $deviceid | cut -d" " -f1 | cut -d":" -f1)
		if [ -z $sdevice ]; then
			echo -e "${ABORT}[FAIL] Dispositivo não encontrado ${NC}"
			exit
		else
			target="/mnt/$deviceid"
			mkdir -p $target
			mount $sdevice $target
			if [ $? != 0 ]; then
				echo -e "${ABORT}[FAIL] Impossível montar dispositivo, por favor, tente montar manualmente ${NC}"
				exit
			else
				echo -e "${GREEN}[OK] Dispositivo da urna montado${NC}"
			fi
		fi
	fi

	validate_system "$target/var/www/html/urna" 1
	valid=$?
	syskey=$(cat "$target/etc/syskey")
	if [ "$syskey" != "$globalmd5" ]; then
		invalidskey="1"
		echo -e "${ERROR} Chave do sistema operacional da urna, inválida ${NC}"
	fi

	get_id $target
	send_files $target $pwd1 $pwd2

	echo 'Gostaria de descarregar outro dispositivo? [y/n]'
	read clone
	if [ $clone == "y" ]; then
		echo -e "${YELLOW}Por favor, insira o novo dispositivo. [PRESSIONE ENTER QUANDO PRONTO]${NC}"
		read
	fi
done

echo -e "FIM"
exit 0
