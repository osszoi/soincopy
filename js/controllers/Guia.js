(function(){
	var Guia = function($scope, $http, $location, $routeParams, $timeout, $window){		
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

		$scope.buscar_status = 0;

		$scope.estados = [
			{
				val: 0,
				nombre: "Pendientes"
			},
			{
				val: 1,
				nombre: "Aprobadas"
			},
			{
				val: -1,
				nombre: "Rechazadas"
			},
			{
				val: 2,
				nombre: "Inactivas"
			}
		];

		$scope.init_agregarguia = function(){
			$scope.agregarguia_titulo = "";
			$scope.agregarguia_carrera = "";
			$scope.agregarguia_materia = "";
			$scope.agregarguia_profesor = "";
			$scope.agregarguia_seccion = "";
			$scope.agregarguia_comentario = "";
			$scope.agregarguia_recibida_por = "";
		}

		$scope.cargar_materias = function(){
			$.ajax({
			    url: "php/run.php?fn=cargar_materias",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			    	var json = $.parseJSON(data);

			        $scope.safeApply(function(){
			        	$scope.materias = json;

			        	// Creo solo las carreras
			        	var cs = [];

			        	for (var i = 0; i < json.length; i++)
			        		if (cs.indexOf(json[i].carrera) == -1)
			        			cs.push(json[i].carrera);

			        	$scope.carreras = cs;
			        })
			    }
			});
		}

		$scope.cargar_profesores = function(){
			$.ajax({
			    url: "php/run.php?fn=cargar_profesores",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.profesores = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.cargar_personal = function(){
			$.ajax({
			    url: "php/run.php?fn=cargar_personal",
			    type: "POST",
			    data: {},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.personal = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.cargar_guias = function(){
			$scope.guias = null;

			var status = $scope.buscar_status;

			$.ajax({
			    url: "php/run.php?fn=cargar_guias",
			    type: "POST",
			    data: {status:status},
			    beforeSend: function(){},
			    success: function(data){
			        $scope.safeApply(function(){
			        	$scope.guias = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.cargar_guia = function(){
			$scope.guia = null;

			var codigo = $scope.modificar_codigo;

			$.ajax({
			    url: "php/run.php?fn=cargar_guia",
			    type: "POST",
			    data: {codigo:codigo},
			    beforeSend: function(){},
			    success: function(data){
			    	console.log($.parseJSON(data))
			        $scope.safeApply(function(){
			        	$scope.guia = $.parseJSON(data);
			        	$scope.guia_aux = $.parseJSON(data);
			        })
			    }
			});
		}

		$scope.eliminar_pdf = function(){
			if (!confirm("Está seguro que desea eliminar el PDF?\nADVERTENCIA: UNA VEZ ELIMINADO NO PODRÁ RECUPERARSE"))
				return;

			var pdf = $scope.guia.pdf;

			$.ajax({
			    url: "php/carga.php?action=delete",
			    type: "POST",
			    data: {file:pdf},
			    beforeSend: function(){},
			    success: function(data){
			        if (data == "ok")
			        {
			        	$scope.safeApply(function(){
			        		$scope.guia.pdf = null;
			        		$scope.upload_progress = 0;
			        	})
			        }
			        else
			        {
			        	alert("Ha ocurrido un error eliminando el archivo.");
			        	console.log(data);
			        }
			    }
			});
		}

		$scope.subir_pdf = function(){
			var file = $(':file')[0].files;

			if (file.length == 0)
			{
				alert("Debe seleccionar un archivo");
				return;
			}

			if (file.length > 1)
			{
				alert("Debe seleccionar solo un archivo");
				return;
			}

			file = file[0];

			if (file.type != "application/pdf")
			{
				alert("Solo se admiten archivos PDF");
				return;
			}

			if (file.size > 100 * 1024 * 1024)
			{
				alert("Este archivo excede el tamaño máximo de archivo (100 MB)");
				return;
			}

			var formData = new FormData();
    		formData.append(file.name, file);

    		$scope.upload_progress = 1;

			$.ajax({
			    url: "php/carga.php?action=upload",
			    type: "POST",
			    data: formData,
			    processData: false,
			    dataType: 'json',
			    contentType: false,
			    beforeSend: function(){
			    	$scope.safeApply(function(){
		    			$scope.upload_progress = 1;
		    		});

		    		$(".progress-bar").addClass("progress-bar-striped").addClass("active");
			    },
			    uploadProgress: function(event, position, total, percentComplete) {
					$scope.safeApply(function(){
						$scope.upload_progress = percentComplete;
					})
				},
			    success: function(data){},
			    complete: function(r){
			    	var json = $.parseJSON(r.responseText);

			    	if (json.success)
			    	{
			    		$(".progress-bar").removeClass("progress-bar-striped").removeClass("active");

			    		$scope.safeApply(function(){
			    			$scope.upload_progress = 100;

			    			if (!$routeParams.codigo)
			    			{
				    			$scope.agregarguia_pdf = json.pdf;
				    			$scope.agregarguia_paginas = json.pages;
				    			$scope.agregarguia_hojas = Math.floor(json.pages / 2) + (json.pages % 2);
			    			}
			    			else
			    			{
			    				$scope.guia.pdf_ = json.pdf;
				    			$scope.guia.numero_paginas = json.pages;
				    			$scope.guia.numero_hojas = Math.floor(json.pages / 2) + (json.pages % 2);
			    			}
			    		})
			    	}
			    }
			});
		}

		$scope.cambiar_estado = function(s){
			var codigo = $scope.guia.codigo;

			$.ajax({
			    url: "php/run.php?fn=cambiar_estado",
			    type: "POST",
			    data: {status:s, codigo:codigo},
			    beforeSend: function(){},
			    success: function(data){
			        if (data == "ok")
			        {
			        	$scope.safeApply(function(){
			        		$scope.guia.status = s;

			        		if (s == -1)
			        			$scope.guia.status_str = "rechazada";
			        		else if (s == 1)
			        			$scope.guia.status_str = "aprobada";
			        		else if (s == 2)
			        			$scope.guia.status_str = "inactiva";
			        	})

			        	if (s == -1)
			        		$(".zona_alertas").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>La guía ha sido rechazada con éxito</div>');
			        	else if (s == 1)
			        		$(".zona_alertas").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>La guía ha sido aprobada con éxito</div>');
			        	else if (s == 2)
			        		$(".zona_alertas").html('<div class="alert alert-info alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>La guía ha sido desactivada con éxito</div>');
			        }
			    }
			});
		}

		$scope.agregar_guia = function(){
			if ($scope.agregarguia_titulo.length == 0 || $scope.agregarguia_materia.length == 0 || $scope.agregarguia_profesor.length == 0 || $scope.agregarguia_seccion.length == 0 || $scope.agregarguia_recibida_por.length == 0)
			{
				alert("Debe llenar todos los campos obligatorios");
				return;
			}

			if (parseInt($(".progress-bar").html()) != 100)
			{
				alert("Debe cargar el archivo PDF antes de agregar la guía");
				return;
			}

			var post = {
				titulo: $scope.agregarguia_titulo,
				materia: $scope.agregarguia_materia,
				profesor: $scope.agregarguia_profesor,
				seccion: $scope.agregarguia_seccion,
				comentario: $scope.agregarguia_comentario,
				entregada_por: $scope.agregarguia_profesor,
				recibida_por: $scope.agregarguia_recibida_por,
				pdf: $scope.agregarguia_pdf,
				hojas: $scope.agregarguia_hojas,
				paginas: $scope.agregarguia_paginas
			};

			$.ajax({
			    url: "php/run.php?fn=agregar_guia",
			    type: "POST",
			    data: post,
			    beforeSend: function(){},
			    success: function(data){
			        if (data == "ok")
			        	$scope.safeApply(function(){
			        		$location.path("/pendientes");
			        	})
			    }
			});
		}

		$scope.modificar_guia = function(){
			if ($scope.guia.titulo.length == 0 || $scope.guia.materia.length == 0 || $scope.guia.profesor.length == 0 || $scope.guia.seccion.length == 0 || $scope.guia.recibida_por.length == 0)
			{
				alert("Debe llenar todos los campos obligatorios");
				return;
			}

			if (parseInt($(".progress-bar").html()) != 100 && !$scope.guia.pdf)
			{
				alert("Debe cargar el archivo PDF antes de agregar la guía");
				return;
			}

			var post = {
				codigo: $scope.guia.codigo,
				titulo: $scope.guia.titulo,
				materia: $scope.guia.materia,
				profesor: $scope.guia.profesor_id,
				seccion: $scope.guia.seccion,
				comentario: $scope.guia.comentario,
				entregada_por: $scope.guia.entregada_por_id,
				recibida_por: $scope.guia.recibida_por_id,
				pdf: $scope.guia.pdf_,
				hojas: $scope.guia.numero_hojas,
				paginas: $scope.guia.numero_paginas
			};

			$.ajax({
			    url: "php/run.php?fn=modificar_guia",
			    type: "POST",
			    data: post,
			    beforeSend: function(){},
			    success: function(data){
			        if (data == "ok")
			        	$scope.safeApply(function(){
			        		$(".zona_alertas").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>La guía ha sido modificada con éxito</div>');

			        		$scope.guia.pdf = $scope.guia.pdf_;
			        	})
			    }
			});
		}

		if ($routeParams.codigo)
		{
			$scope.modificar_codigo = $routeParams.codigo;
			$scope.cargar_guia();
		}
	};

	angular.module("soincopy").controller("Guia", Guia);
}());