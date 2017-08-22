'use strict';
app
	.service('PaymentModal',function($uibModal,$translate,InvoiceResource,$http){
		/**
		 * 1 = credit card
		 * 2 = boleto
		 * 3 = paypal
		 */
		var _self = this;
		var init = function(){
			_self.plan = null;
			_self.card = null;
			_self.address = null;
			_self.payment = null;	
			_self.billing = null;	
			_self.modal = null;
		}
		init();
		_self.moipPublicKey = '-----BEGIN PUBLIC KEY-----\n'+
								'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxcXo7yJjtyx0T3yCIzuD\n'+
								'Oyz3ZcGRLngS1ajFGmPjLrjIKyCAk/Dwb0sUNuuHLmnPfRSCzSUWtr6V3TF+9lsu\n'+
								'DDtbbKTnPYGWPLFtFSmQ/e8ZmBKn3OcgnXqodD9MjKQQn99CMRt0wct6AGGVAdjc\n'+
								'ceHdjpPmrR4m1bS88pOs/WAOuWYdDoxYVKRxblXE0NRx0Kq8T6CCUoztQR8QRth2\n'+
								'Fwv3D/2jKppCBiDKfhrmfAtFpUZTQgHmbPt77e8R12123RaOUzv0fsdomPdmmmkB\n'+
								'w/EdcmqOCCzcvKBQvgfPtlCmn/SpyCqDv+P3LTODtc9rkwxOVE1qrqiNUi8TBPLv\n'+
								'LQIDAQAB\n'+
								'-----END PUBLIC KEY-----';
    _self.planList = [
        {id:1,value:0.0218,init:500,end:999},
        {id:2,value:0.0189,init:1000,end:2499},
        {id:3,value:0.01756,init:2500,end:4999},
        {id:4,value:0.01598,init:5000,end:9999},
        {id:5,value:0.01299,init:10000,end:19999},
        {id:6,value:0.009996,init:20000,end:24999},
        {id:7,value:0.009995,init:25000,end:49999},
        {id:8,value:0.008798,init:50000,end:74999},
        {id:9,value:0.007865,init:75000,end:99999},
        {id:10,value:0.006799,init:100000,end:124999},
        {id:11,value:0.006332,init:125000,end:149999},
        {id:12,value:0.005999,init:150000,end:199999},
        {id:13,value:0.005598,init:200000,end:249999},
        {id:14,value:0.005597,init:250000,end:499999},
        {id:15,value:0.004860,init:500000,end:1000000}
    ];

    var validate = function(form){
        $(form).find('div[class^="form-group"]').removeClass("has-error");
        var validation = {
            error : false,
            msg : "",
            fields : []
        };
        $(form+" *[required]").each(function(v){
            if ((!$(this).val() || $(this).val()=="") && $(this).is(":visible")) {
                validation.error = true;
                validation.fields.push("#"+$(this).prop("id"));
            }
        });
        validation.msg += (validation.error?"Preencha todos os campos obrigatórios":"");
        if(validation.error){
            swal("Verifique os erros abaixo",validation.msg,"error");
            if(validation.fields.length>0){
                $.each(validation.fields,function(k,v){
                    $(form+" "+v).closest('div[class^="form-group"]').addClass("has-error");
                });
            }
            return false;
        }
        return true;
    }

    _self.startPayment = function(plan){
    	init();
    	_self.selectPayment(plan);
    }

    _self.selectPayment = function(plan){
        if(plan){
            _self.plan = angular.copy(plan);
        }
        var modalInstance = $uibModal.open({
          animation: true,
          ariaLabelledBy: 'modal-title',
          ariaDescribedBy: 'modal-body',
          templateUrl: 'views/tmpl/pages/payment/select_payment.html',
          size: 'md',
          controller: function($scope,$uibModalInstance,$filter){
          	_self.modal = $uibModalInstance;
            $scope.plan = _self.plan;
            $scope.selectedPayment = _self.payment;
            $scope.$watch("plan.credits",function(){
                var newPlan = $filter("betweenValues")(_self.planList,$scope.plan.credits,'init','end');
                if(newPlan.length>0){
                    newPlan = newPlan[0];
                }
                else{
                    newPlan = {value: 0, credits:0};
                }
                $scope.plan.value = newPlan.value; // mudar aqui pra pegar o plano conforme a nova quantidade
                $scope.plan.total = $scope.plan.credits*$scope.plan.value;
            });
            $scope.select = function(payment){
                $scope.selectedPayment = payment;
                _self.payment = $scope.selectedPayment;
            }
            $scope.nextPayment = function(){
                if(!_self.payment){
                    swal($translate.instant("financial.no_payment"),$translate.instant("financial.no_payment"),"error");
                }
                else{
                	_self.plan = $scope.plan;
                  _self.modal.close();
                }
            }
          }
      });

      modalInstance.result.then(function () {
        if(_self.payment){
            switch(_self.payment){
                case "1":
                    _self.fillCard();
                break;
                case "2":
                    _self.fillBoleto();
                break;
                case "3":
                    _self.confirmPayment();
                break;
            }
        }
      });
    };

    _self.loadCep = function($scope){
        if($scope.address.zip_code){
	        $http({
	            method: 'GET',
	            url: 'http://viacep.com.br/ws/'+$scope.address.zip_code+'/json/',
	            headers: { 
	                'Authorization': undefined
	            }
	          }).then(function successCallback(response) {
	              var end = response.data;
	              if(!end.erro){
				          $scope.address.street = end.logradouro;
				          $scope.address.neighborhood = end.bairro;
				          $scope.address.state = end.uf;
				          $scope.address.city = end.localidade;
	              }
	          },
	          function(){
	            return false;
	          });
        }
    }
    _self.fillCard = function(){
        var modalInstance = $uibModal.open({
          animation: true,
          templateUrl: 'views/tmpl/pages/payment/credit_card.html',
          windowClass: 'credit-card-div',
          controller: function($scope,$uibModalInstance){
          	_self.modal = $uibModalInstance;
          	$scope.card = _self.card;
          	$scope.address = _self.address;
          	$scope.billing = _self.billing;
            $scope.cancel = function(){
              _self.modal.close();
            }
            $scope.paynow = function(){
              if(validate("#form-card")){
                _self.card = $scope.card;
                _self.address = $scope.address;
                _self.billing = $scope.billing;
                _self.modal.close("confirm"); 
              }
            }
            $scope.loadCep = function(){
            	return _self.loadCep($scope);
            }
          },
          size: 'md',
          resolve: {

          }
        });
        modalInstance.result.then(function (selectedItem) {
          if(selectedItem){
              switch(selectedItem){
                  case "confirm":
                    _self.confirmPayment();
                  break;
              }
          }
          else{
            _self.selectPayment();
          }

        },
        function(){
          _self.selectPayment();
        });
    };

    var getCardHash = function(){
        var cc = new Moip.CreditCard({
          number  : _self.card.cardNumber,
          cvc     : _self.card.cardCvv,
          expMonth: _self.card.cardMonth,
          expYear : _self.card.cardYear,
          pubKey  : _self.moipPublicKey
        });
        if( cc.isValid()){
        	return cc.hash();
        }
        else{
        	return false;
        }
    }

    _self.fillBoleto = function(){
        var modalInstance = $uibModal.open({
          animation: true,
          templateUrl: 'views/tmpl/pages/payment/boleto.html',
          windowClass: 'credit-card-div',
          controller: function($scope,$uibModalInstance){
          	_self.modal = $uibModalInstance;
          	$scope.address = _self.address;
          	$scope.billing = _self.billing;
            $scope.cancel = function(){
              _self.modal.close();
            }
            $scope.paynow = function(){
              if(validate("#form-boleto")){
                _self.address = $scope.address;
                _self.billing = $scope.billing;
                _self.modal.close("confirm"); 
              }
            }
            $scope.loadCep = function(){
            	return _self.loadCep($scope);
            }
          },
          size: 'md',
          resolve: {

          }
        });
        modalInstance.result.then(function (selectedItem) {
          if(selectedItem){
              switch(selectedItem){
                  case "confirm":
                    _self.confirmPayment();
                  break;
              }
          }
          else{
            _self.selectPayment();
          }

        },
        function(){
          _self.selectPayment();
        });
    };

    var payWithCard = function(){
    	var cardHash = getCardHash();
    	if(!cardHash){
        swal($translate.instant("financial.credit_card.card_error"),$translate.instant("financial.credit_card.invalid_card"),"error");
        return;
    	}
    	var invoice = new InvoiceResource();
    	invoice.card = {card_token : cardHash};
    	invoice.address = _self.address;
    	invoice.payment = {
    		payment_type : _self.payment,
	    	quantity : _self.plan.credits,
	    	birthdate : moment(_self.billing.birth_date,"DD/MM/YYYY").format("YYYY-MM-DD"),
	    	document : _self.billing.cpf_cnpj,
	    	document_type: (_self.billing.cpf_cnpj.length>11?"CNPJ":"CPF")
    	};
    	invoice.$save({}, function(data){
    		init();
        swal($translate.instant("financial.success.credit_card.title"),$translate.instant("financial.success.credit_card.description"),"success");
        _self.modal.close();
    	},
    	function(){
        swal($translate.instant("financial.error.credit_card.title"),$translate.instant("financial.error.credit_card.description"),"error");
    	});
    }
    var payWithBoleto = function(){
    	var invoice = new InvoiceResource();
    	invoice.address = _self.address;
    	invoice.payment = {
    		full_name : _self.full_name,
    		payment_type : _self.payment,
	    	quantity : _self.plan.credits,
	    	birthdate : moment(_self.billing.birth_date,"DD/MM/YYYY").format("YYYY-MM-DD"),
	    	document : _self.billing.cpf_cnpj,
	    	document_type: (_self.billing.cpf_cnpj.length>11?"CNPJ":"CPF")
    	};
    	invoice.$save({}, function(data){
    		init();
      	swal({
      		title : $translate.instant("financial.success.boleto.title"),
      		type:"success",
      		html:$translate.instant("financial.success.boleto.description")+"<br><a href='"+invoice.payment.link+"' target='_BLANK'/>"+$translate.instant("financial.boleto.print")+"</a>"
      	});
      	window.open(invoice.payment.link,"_BLANK","width=800");
    	},
    	function(){
      	swal($translate.instant("financial.error.boleto.title"),$translate.instant("financial.error.boleto.description"),"error");
    	});
      _self.modal.close();
      init();
    }
    var payWithPaypal = function(){
      _self.modal.close();
      swal($translate.instant("financial.success.paypal.title"),$translate.instant("financial.success.paypal.description"),"success");
  		init();
    }

    _self.confirmPayment = function(){
        var modalInstance = $uibModal.open({
          animation: true,
          templateUrl: 'views/tmpl/pages/payment/confirm.html',
          windowClass: 'credit-card-div',
          controller: function($scope,$uibModalInstance){
          	_self.modal = $uibModalInstance;
          	$scope.plan = _self.plan;
          	$scope.payNow = function(){
          		switch(_self.payment){
                case "1":
                  payWithCard();
                break;
                case "2":
                	payWithBoleto();
                break;
                case "3":
                  payWithPaypal();
                break;
              }
          	}
            $scope.cancel = function(){
              _self.modal.close();
            }
          },
          size: 'md',
          resolve: {

          }
        });
        modalInstance.result.then(function (selectedItem) {
          if(selectedItem){
              switch(selectedItem){
                  case "selectPayment":
                      _self.selectPayment();
                  break;
              }
          }
          else{
        		switch(_self.payment){
        			case "1":
            		_self.fillCard();
            	break;
        			case "2":
            		_self.fillBoleto();
            	break;
        			case "3":
            		_self.selectPayment();
            	break;
          	}
          }
        },
        function(){
          _self.selectPayment();
        });
    }

	});