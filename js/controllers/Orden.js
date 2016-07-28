(function(){
	var Orden = function($scope, $http, $location, $routeParams, $timeout, $window, AlertService, SoincopyService)
	{		
		$scope.safeApply = function(fn) {
		    var phase = this.$root.$$phase;
		    if(phase == '$apply' || phase == '$digest') {
		        if(fn && (typeof(fn) === 'function')) {
		          fn();
		        }
		    } else {
		       this.$apply(fn);
		    }
		};

		$scope.editar = $routeParams.id;

		SoincopyService.getOrdenes($scope);
		SoincopyService.getDependencias($scope);
		SoincopyService.getDepartamentosUCAB($scope);
		SoincopyService.getProductosOriginales($scope);

		$scope.cargar_orden = function(id){
			SoincopyService.getOrden($scope, id);
		}

		$scope.registrar_orden = function(){
			$.confirm({
				title: 'Confirmar acción',
				content: '¿Está seguro que desea añadir esta orden?',
				confirm: function(){
					var post = $scope.orden;
					post.fecha_inicio_ = post.fecha_inicio.toJSON().slice(0,10);
					post.fecha_fin_ = post.fecha_fin.toJSON().slice(0,10);

					var fn = "agregar_orden";
					var msg = "Orden añadida con éxito";

					if ($routeParams.id)
					{
						fn = "editar_orden";
						msg = "Orden modificada con éxito";
					}

					$.ajax({
					    url: "php/run.php?fn=" + fn,
					    type: "POST",
					    data: post,
					    beforeSend: function(){},
					    success: function(data){
					    	console.log(data)
					        if (data == "ok")
					        	$scope.safeApply(function(){
					        		AlertService.showSuccess("Orden añadida con éxito");
					        		$location.path("/ordenes");
					        	})
					    }
					});
				},
				cancel: function(){}
			});
		}

		$scope.cambiar_estado = function(id, estado){
			$.ajax({
			    url: "php/run.php?fn=cambiar_estado_orden",
			    type: "POST",
			    data: {id:id, estado:estado},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	SoincopyService.getOrdenes($scope);
			        })
			    }
			});
		}

		if ($routeParams.id)
		{
			$scope.cargar_orden($routeParams.id);
		}
	};

	angular.module("soincopy").controller("Orden", Orden);
}());