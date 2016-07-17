(function(){
	var Materia = function($scope, $http, $location, $routeParams, $timeout, $window){		
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

		$scope.cargar_materia = function(id){
			$.ajax({
			    url: "php/run.php?fn=cargar_materias",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	var json = $.parseJSON(data);

			        	for (var i = 0; i < json.length; i++)
			        		if (json[i].id == id)
			        			$scope.materia = json[i];

			        	$scope.cargar_periodos();
			        })
			    }
			});
		}

		$scope.cargar_periodos = function(){
			var cid = $scope.materia.carrera_id ? $scope.materia.carrera_id : $scope.materia.carrera;

			$.ajax({
			    url: "php/run.php?fn=cargar_periodos",
			    type: "POST",
			    data: {cid:cid},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.periodos = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.cargar_materias = function(){
			$.ajax({
			    url: "php/run.php?fn=cargar_materias",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.materias = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.cargar_carreras = function(){
			$.ajax({
			    url: "php/run.php?fn=cargar_carreras",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.carreras = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.registrar_materia = function(){
			var post = $scope.materia;

			var fn = "agregar_materia";

			if ($routeParams.id)
				fn = "editar_materia";

			$.ajax({
			    url: "php/run.php?fn=" + fn,
			    type: "POST",
			    data: post,
			    beforeSend: function(){},
			    success: function(data){
			    	console.log(data)
		        	$scope.safeApply(function(){
		        		$location.path("/materias");
		        	})
			    }
			});
		}

		$scope.cambiar_estado = function(id, estado){
			$.ajax({
			    url: "php/run.php?fn=cambiar_estado_materia",
			    type: "POST",
			    data: {id:id, estado:estado},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.cargar_materias();
			        })
			    }
			});
		}

		if ($routeParams.id)
		{
			$scope.cargar_materia($routeParams.id);
		}
	};

	angular.module("soincopy").controller("Materia", Materia);
}());