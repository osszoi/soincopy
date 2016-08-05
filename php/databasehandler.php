﻿<?php
	class DatabaseHandler
	{
        /* Documentacion PDO: 
        *  http://php.net/manual/es/book.pdo.php
        */

        // local, main, test
        private $connect_to = "local";

		private $db;

        private $session_duration = 300;

        public function __construct()
        {
            if ($this->connect_to == "local")
            {
                $this->username = "root";
                $this->password = "21115476";
                $this->dsn = "mysql:dbname=soincopy;host=localhost";
            }
            elseif ($this->connect_to == "main")
            {
                $this->username = "salazars_eduardo";
                $this->password = "21115476";
                $this->dsn = "mysql:dbname=salazars_soincopy;host=localhost";
            }
            elseif ($this->connect_to == "test")
            {
                $this->username = "folkanda_admin";
                $this->password = "dEusk28dnAuskedg";
                $this->dsn = "mysql:dbname=folkanda_test;host=localhost";
            }

            $this->connect();
        }

		public function connect()
        {
            if (!$this->db instanceof PDO)
            {
                $this->db = new PDO($this->dsn, $this->username, $this->password);       
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }

            $this->db->query("SET CHARSET utf8");
        }

        public function dbDeleteAll()
        {
            $fileName = "../bd/script-drops.sql";
            $fp = fopen($fileName, "r");
            $content = fread($fp, filesize($fileName));
            fclose($fp);
            

            $query = $content;

            $this->connect();
            $this->db->query($query);
        }

        public function dbCreateAll()
        {
            $fileName = "../bd/script-creacion.sql";
            $fp = fopen($fileName, "r");
            $content = fread($fp, filesize($fileName));
            fclose($fp);
            

            $query = $content;

            $this->connect();
            $this->db->query($query);
        }

        public function dbAddBasicData()
        {
            $fileName = "../bd/script-datosiniciales.sql";
            $fp = fopen($fileName, "r");
            $content = fread($fp, filesize($fileName));
            fclose($fp);


            $query = $content;

            $this->connect();
            $this->db->query($query);
        }

        public function cleanImagesFolder()
        {
            $files = glob('../images/*'); // get all file names
            foreach($files as $file){ // iterate files
              if(is_file($file))
                unlink($file); // delete file
            }
        }










        public function fixAntiSqlInject($text)
        {
            $text = str_replace("\"", "", $text);
            $text = str_replace("'", "", $text);
            return $text;
        }

        private $KEY = "oSsZoInSsCoToO";
    
        /**
         * Encriptar un string 
         * 
         * @param string $string El string que queremos encriptar
         * 
         * @return String
         */
        function encrypt($string) 
        {
            $iv = md5(md5($this->KEY));
            
            $enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->KEY), $string, MCRYPT_MODE_CBC, $iv);
            $enc = base64_encode($enc);
            
            return $enc;
        }

        /**
         * Encriptar un string 
         * 
         * @param string $string El string que queremos encriptar
         * 
         * @return String
         */
        function encryptForDb($string) 
        {
            $iv = md5(md5($this->KEY));
            
            $enc = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->KEY), $string, MCRYPT_MODE_CBC, $iv);
            $enc = base64_encode($enc);

            $enc = $this->fixAntiSqlInject($enc);
            
            return $enc;
        }
        
        /**
         * Desencripta un string
         * 
         * @param string $string El string que vamos a desencriptar
         */
        function decrypt($string) 
        {
            $iv = md5(md5($this->KEY));
            
            $dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->KEY), base64_decode($string), MCRYPT_MODE_CBC, $iv);
            $dec = rtrim($dec, "");
            
            return $dec;
        }

        public function getTodayDate()
		{
			date_default_timezone_set('America/Caracas');
			$today = array(
				"ano" => (int)date('Y', time()),
				"mes" => (int)date('m', time()),
				"dia" => (int)date('d', time()),
			);
			
			return $today;
		}

        public function getTodayDateString()
        {
            date_default_timezone_set('America/Caracas');
            $today = array(
                "ano" => (int)date('Y', time()),
                "mes" => (int)date('m', time()),
                "dia" => (int)date('d', time()),
            );
            
            return $today['dia'] . '-' . $today['mes'] . '-' . $today['ano'];
        }

        public function getTodayDateStringDB()
        {
            date_default_timezone_set('America/Caracas');
            $today = array(
                "ano" => (int)date('Y', time()),
                "mes" => (int)date('m', time()),
                "dia" => (int)date('d', time()),
            );
            
            return $today['ano'] . '-' . $today['mes'] . '-' . $today['dia'];
        }






        /* Funciones ejemplo */

        public function ejemploInsert($nombre, $apellido)
        {
            $query = $this->db->prepare("
                INSERT INTO Persona (nombre, apellido)
                VALUES (':nombre', ':apellido')
            ");

            $query->execute(array(
                ":nombre" => $nombre,
                ":apellido" => $apellido
            ));

            // Ejemplo obtener el id de eso que acabamos de añadir
            $ultimoIdAnadido = $this->db->lastInsertId();
        }

        public function ejemploLeer()
        {
            $query = $this->db->prepare("SELECT * FROM Persona");
            $query->execute(); 
            // En este punto $query es un objeto de PDO
            // Sin embargo aun no contiene lo que pedimos
            // Para ello hacemos:
            $datos = $query->fetchAll();
            // fetchAll devuelve un arreglo con las filas de respuesta
            // No es recomendable cambiar el valor de $query
            // Por ejemplo: $query = $query->fetchAll()
            // Ya que perderiamos la posibilidad de obtener cosas como:
            // La cantidad de filas respuesta:
            $nroFilasRespuesta = $query->rowCount();
            // Asi como tambien la posibilidad de recorrerlo con un foreach
            foreach ($query as $filaRespuesta)
            {
                // algo
            }
        }





        /* Funciones nuevas aqui abajo */
        public function actualizar_hora_sesion()
        {
            @session_start();
            $_SESSION['login_time'] = time();
        }

        public function session_expired()
        {
            @session_start();

            if (!isset($_SESSION['login_time']))
                return true;

            if (time() - $_SESSION['login_time'] > $this->session_duration)
                return true;

            return false;
        }

        public function login($post)
        {
            $query = $this->db->prepare("
                select u.id as id, u.usuario as username, u.nombre as nombre, u.apellido as apellido, u.cedula as cedula, u.email as email, u.telefono as tlf
                from Personal as u
                where u.usuario=:username and u.contrasena=:password and u.estado=1
                limit 1
            ");

            $query->execute(array(
                ":username" => $post['username'],
                ":password" => $post['password']
            ));

            $u = $query->fetchAll();

            if (count($u) > 0)
            {
                /* Obtengo los permisos */
                $user = $u[0];

                $query_root = "
                    select nombre
                    from Permiso as p
                ";

                $query_no_root = "
                    select nombre
                    from Permiso_Asignado as pa, Permiso as p
                    where pa.permiso=p.id and pa.usuario=:uid
                ";

                $query = $this->db->prepare($post['username'] == "root" ? $query_root : $query_no_root);

                $query->execute(array(
                    ":uid" => $user['id']
                ));

                $permisos = $query->fetchAll();

                foreach ($permisos as $p)
                    $user[$p['nombre']] = 1;

                /* Obtengo la ultima conexion */
                $query = $this->db->prepare("
                    select date_format(l.fecha, '%d/%m/%Y') as fecha, time_format(l.fecha, '%h:%i:%s %p') as hora
                    from Log_Login as l
                    where username=:username
                    order by l.fecha desc
                    limit 1
                ");

                $query->execute(array(
                    ":username" => $post['username']
                ));

                $ult = $query->fetchAll();
                $user['ultima_visita'] = $ult[0];

                /* Setteo la sesion y registro el login */
                @session_start();
                $_SESSION['login_username'] = $post['username'];
                $this->actualizar_hora_sesion();

                $query = $this->db->prepare("
                    insert into Log_Login (fecha, username)
                    values (now(), :username)
                ");

                $query->execute(array(
                    ":username" => $post['username']
                ));

                return json_encode($user);
            }
            else
                return json_encode(array("error" => 1));
        }

        public function cargar_materias_carrera($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, c.id as carrera_id, p.tipo as tipo_carrera, m.estado as estado, p.id as periodo_id, t.nombre as tipo, t.nombre as tipo_nombre, t.id as tipo_id
                from Materia as m, Car_Per as cp, Carrera as c, Periodo as p, Tipo_Materia as t
                where m.dictada_en=cp.id and cp.carrera=c.id and cp.periodo=p.id and m.tipo=t.id and c.id=:cid and m.estado=1
                order by p.numero asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_materias($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, c.id as carrera_id, p.tipo as tipo_carrera, m.estado as estado, cp.id as periodo_id, t.nombre as tipo, t.nombre as tipo_nombre, t.id as tipo_id
                from Materia as m, Car_Per as cp, Carrera as c, Periodo as p, Tipo_Materia as t
                where m.dictada_en=cp.id and cp.carrera=c.id and cp.periodo=p.id and m.tipo=t.id
                order by p.numero asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_menciones($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, c.id as cid, m.estado as estado
                from Mencion as m, Carrera as c
                where m.carrera=c.id
                order by c.nombre asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_menciones_de($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, c.nombre as carrera, c.id as cid, m.estado as estado
                from Mencion as m, Carrera as c
                where m.carrera=c.id and c.id=:cid
                order by c.nombre asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_carreras($post)
        {
            $query = $this->db->prepare("
                select c.id as id, c.nombre as nombre, c.estado as estado
                from Carrera as c
                order by c.nombre asc
            ");

            $query->execute();
            $carreras = $query->fetchAll();

            for ($i = 0; $i < count($carreras); $i++)
            {
                $query = $this->db->prepare("
                    select p.tipo as tipo
                    from Car_Per as cp, Periodo as p
                    where cp.periodo=p.id and cp.carrera=:cid
                    limit 1
                ");

                $query->execute(array(
                    ":cid" => $carreras[$i]['id']
                ));

                $tipo = $query->fetchAll();
                $tipo = $tipo[0]['tipo'];
                $carreras[$i]['tipo'] = $tipo;
            }

            return json_encode($carreras);
        }

        public function cargar_tipos_materias($post)
        {
            $query = $this->db->prepare("
                select *
                from Tipo_Materia
                order by id asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_condiciones_pago($post)
        {
            $query = $this->db->prepare("
                select *
                from Condicion_Pago
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_cuentaabiertas($post)
        {
            $query = $this->db->prepare("
                select id, nombre, vence, estado, (case when vence is not null then date_format(vence, '%d/%m/%Y') else 'Nunca' end) as vence_el, (case when vence is not null then (curdate() > vence) else 0 end) as vencido
                from CuentaAbierta
                order by id asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_inventario($post)
        {
            $query = $this->db->prepare("
                select m.id as id, m.nombre as nombre, m.estado as estado, (select (case when sum(cantidad) is not null then sum(cantidad) else 0 end) from Stock where material=m.id and eliminado=0) as cantidad, (select concat(date_format(fecha_anadido, '%d/%m/%Y'), ' a las ', time_format(fecha_anadido, '%h:%i:%s %p')) from Stock where material=m.id and eliminado=0 order by fecha_anadido desc limit 1) as fecha_ultimo_ingreso
                from Material as m
            ");

            $query->execute();
            $inventario = $query->fetchAll();

            for ($i = 0; $i < count($inventario); $i++)
            {
                $inventario[$i]['stock'] = array();

                $query = $this->db->prepare("
                    select s.id as id, s.cantidad as cantidad, s.fecha_anadido as fecha_anadido, s.costo as costo, concat(date_format(s.fecha_anadido, '%d/%m/%Y'), ' a las ', time_format(s.fecha_anadido, '%h:%i:%s %p')) as fecha_str
                    from Stock as s
                    where s.material=:mid and s.eliminado=0
                    order by fecha_anadido desc
                ");

                $query->execute(array(
                    ":mid" => $inventario[$i]['id']
                ));

                $inventario[$i]['stock'] = $query->fetchAll();
            }

            return json_encode($inventario);
        }

        public function cargar_productos($post)
        {
            $productos = array();

            if (!isset($post['did']))
            {
                $query = $this->db->prepare("
                    select p.nombre as nombre, p.id as id, p.descripcion as descripcion, p.estado as estado, d.nombre as departamento_nombre, d.id as departamento, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario
                    from Producto as p, Departamento as d
                    where p.departamento=d.id
                    order by p.nombre asc
                ");

                $query->execute();

                $productos = $query->fetchAll();
            }
            else
            {
                $query = $this->db->prepare("
                    select p.nombre as nombre, p.id as id, p.descripcion as descripcion, p.estado as estado, d.nombre as departamento_nombre, d.id as departamento, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario
                    from Producto as p, Departamento as d
                    order by p.nombre asc
                ");

                $query->execute(array(
                    ":did" => $post['did']
                ));

                $productos = $query->fetchAll();
            }

            for ($i = 0; $i < count($productos); $i++)
            {
                /* Historial de precios */
                $productos[$i]['historial_costos'] = array();

                $query = $this->db->prepare("
                    select pc.id, costo, date_format(pc.fecha, '%d/%m/%Y') as fecha, time_format(pc.fecha, '%h:%i:%s %p') as hora
                    from Producto_Costo as pc
                    where eliminado=0 and producto=:pid
                    order by pc.fecha desc
                ");

                $query->execute(array(
                    ":pid" => $productos[$i]['id']
                ));

                $productos[$i]['historial_costos'] = $query->fetchAll();

                /* Materiales */
                $productos[$i]['materiales'] = array();

                $query = $this->db->prepare("
                    select m.id as material, pm.cantidad as cantidad
                    from Producto_Material as pm, Material as m
                    where pm.material=m.id and pm.producto=:pid
                ");

                $query->execute(array(
                    ":pid" => $productos[$i]['id']
                ));

                $materiales = $query->fetchAll();

                foreach ($materiales as $p)
                {
                    $nuevo = array();

                    $nuevo['material'] = $p['material'];
                    $nuevo['cantidad'] = intval($p['cantidad']);

                    $productos[$i]['materiales'][] = $nuevo;
                }
            }

            return json_encode($productos);
        }

        public function cargar_departamentos($post)
        {
            $query = $this->db->prepare("
                select *
                from Departamento
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_departamentos_ucab($post)
        {
            $query = $this->db->prepare("
                select *
                from Departamento_UCAB
                order by nombre asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_tipos_guias($post)
        {
            $query = $this->db->prepare("
                select *
                from Tipo_Guia
                order by id asc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_profesores($post)
        {
            $query = $this->db->prepare("
                select id, nombre, apellido, cedula, telefono, concat(nombre, ' ', apellido) as nombre_completo, estado, email
                from Profesor
                order by nombre asc;
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_ordenes($post)
        {
            $query = $this->db->prepare("
                select o.id as id, o.numero as numero, du.nombre as departamento, d.nombre as dependencia, o.destino as destino, o.observaciones as observaciones, o.estado as estado, du.id as dpto_ucab, d.id as did, (select (case when sum(precio_total) is not null then sum(precio_total) else 0 end) as total from Orden_Producto where orden=o.id) as costo_total, date_format(o.fecha_modificada, '%d/%m/%Y') as fecha_modificada, o.procesada as procesada, c.nombre as cuentaabierta, c.id as destino
                from Orden as o, Departamento_UCAB as du, Dependencia as d, CuentaAbierta as c
                where o.dpto_ucab=du.id and o.dependencia=d.id and o.destino=c.id
                order by o.id desc
            ");
            $query->execute();

            $ordenes = $query->fetchAll();

            for ($i = 0; $i < count($ordenes); $i++)
            {
                /* Productos */
                $ordenes[$i]['productos'] = array();

                $query = $this->db->prepare("
                    select p.id as producto, op.nro_copias as nro_copias, op.nro_originales as nro_originales, (select costo from Producto_Costo where producto=p.id and eliminado=0 order by fecha desc limit 1) as costo_unitario, op.precio_unitario as costo_unitario_facturado, op.precio_total as costo_total_facturado
                    from Orden_Producto as op, Producto as p
                    where op.producto=p.id
                    and op.orden=:orden
                ");

                $query->execute(array(
                    ":orden" => $ordenes[$i]['id']
                ));

                $productos = $query->fetchAll();

                foreach ($productos as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['copias'] = $p['nro_copias'];
                    $nuevo['originales'] = $p['nro_originales'];
                    $nuevo['costo_unitario'] = $p['costo_unitario'];
                    $nuevo['costo_unitario_facturado'] = $p['costo_unitario_facturado'];
                    $nuevo['costo_total_facturado'] = $p['costo_total_facturado'];

                    $ordenes[$i]['productos'][] = $nuevo;
                }

                /* Personas */
                $ordenes[$i]['personas'] = array();

                $query = $this->db->prepare("
                    select id, nombre_completo, nombre_completo as nombre, cedula as cedula
                    from Persona_Autorizada as p
                    where p.orden=:orden
                ");

                $query->execute(array(
                    ":orden" => $ordenes[$i]['id']
                ));

                $personas = $query->fetchAll();
                $ordenes[$i]['personas'] = $personas;

                /*foreach ($personas as $p)
                {
                    $nuevo = array();

                    $nuevo['producto'] = $p['producto'];
                    $nuevo['copias'] = $p['nro_copias'];
                    $nuevo['originales'] = $p['nro_originales'];

                    $ordenes[$i]['personas'][] = $nuevo;
                }*/
            }

            return json_encode($ordenes);
        }

        public function cargar_dependencias($post)
        {
            $query = $this->db->prepare("
                select * from Dependencia
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cambiar_estado_personal($post)
        {
            $query = $this->db->prepare("
                update Personal set estado=:estado where id=:pid
            ");

            $query->execute(array(
                ":pid" => $post['pid'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                update Departamento_UCAB set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_producto($post)
        {
            $query = $this->db->prepare("
                update Producto set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_dependencia($post)
        {
            $query = $this->db->prepare("
                update Dependencia set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_material($post)
        {
            $query = $this->db->prepare("
                update Material set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function eliminar_stock($post)
        {
            $query = $this->db->prepare("
                update Stock set eliminado=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function eliminar_precio_producto($post)
        {
            $query = $this->db->prepare("
                update Producto_Costo set eliminado=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function cambiar_estado_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                update CuentaAbierta set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_orden($post)
        {
            $query = $this->db->prepare("
                update Orden set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_profesor($post)
        {
            $query = $this->db->prepare("
                update Profesor set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_materia($post)
        {
            $query = $this->db->prepare("
                update Materia set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cambiar_estado_carrera($post)
        {
            $query = $this->db->prepare("
                update Carrera set estado=:estado where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id'],
                ":estado" => $post['estado']
            ));
        }

        public function cargar_periodos($post)
        {
            $query = $this->db->prepare("
                select cp.id as id, (case when p.numero=99 then 'Otro' else p.numero end) as periodo, p.tipo as tipo
                from Car_Per as cp, Periodo as p
                where cp.periodo=p.id and cp.carrera=:cid
                order by p.numero asc
            ");

            $query->execute(array(
                ":cid" => $post['cid']
            ));

            return json_encode($query->fetchAll());
        }

        public function cargar_personal($post)
        {
            $query = $this->db->prepare("
                select *, concat(nombre, ' ', apellido) as nombre_completo
                from Personal
                order by nombre asc
            ");

            $query->execute();
            $ret = $query->fetchAll();

            /* Permisos */
            for ($i = 0; $i < count($ret); $i++)
            {
                $ret[$i]["permisos"] = "";
                $ret[$i]["snombre"] = $ret[$i]["segundo_nombre"];
                $ret[$i]["sapellido"] = $ret[$i]["segundo_apellido"];

                $query = $this->db->prepare("
                    select p.id as id
                    from Permiso_Asignado as pa, Permiso as p
                    where pa.permiso=p.id and usuario=:usuario
                ");

                $query->execute(array(
                    ":usuario" => $ret[$i]['id']
                ));

                $permisos = $query->fetchAll();

                foreach ($permisos as $p)
                    $ret[$i]["permisos"] .= "[" . $p['id'] . "]";
            }

            return json_encode($ret);
        }

        public function cargar_permisos($post)
        {
            $query = $this->db->prepare("
                select p.id as id, p.nombre as nombre, p.descripcion as descripcion, p.riesgo as riesgo, pc.nombre as categoria
                from Permiso as p, Permiso_Categoria as pc
                where p.categoria=pc.id
                order by pc.id asc
            ");
            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_profesor($id)
        {
            $query = $this->db->prepare("call obtener_profesor(:id)");

            $query->execute(array(
                ":id" => $id
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function cargar_personal_($id)
        {
            $query = $this->db->prepare("call obtener_personal_id(:id)");
            
            $query->execute(array(
                ":id" => $id
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function obtener_carrera_desde_materia($id_materia)
        {
            $query = $this->db->prepare("
                select c.id as id, c.nombre as nombre
                from Materia as m, Car_Per as cp, Carrera as c
                where m.dictada_en=cp.id and cp.carrera=c.id and m.id=:id
            ");
            
            $query->execute(array(
                ":id" => $id_materia
            ));

            $d = $query->fetchAll();
            $d = $d[0];

            return $d;
        }

        public function borrar_guia_web($post)
        {
            unlink("../../soincopy_files/guias_web/" . $post['file']);
            $query = $this->db->prepare("
                update Guia_Web set revisada=1 where id=:id
            ");

            $query->execute(array(
                ":id" => $post['id']
            ));
        }

        public function cargar_guias_web($post)
        {
            $query = $this->db->prepare("
                select *, date_format(fecha, '%d/%m/%Y') as fecha_arreglada, time_format(fecha, '%h:%i:%s %p') as hora
                from Guia_Web
                where revisada=0
                order by fecha desc
            ");

            $query->execute();

            return json_encode($query->fetchAll());
        }

        public function cargar_planes_de_estudio($post)
        {
            $query = $this->db->prepare("
                select date_format(pe.fecha, '%d/%m/%Y') as fecha, time_format(pe.fecha, '%h:%i:%s %p') as hora, pe.id as id, pe.titulo as titulo, c.id as carrera_id, c.nombre as carrera, pe.mencion as mencion_id, pe.materia as materia_id, pe.tipo as tipo, pe.pdf as pdf, pe.paginas as paginas, pe.hojas as hojas
                from Plan_de_Estudio as pe, Carrera as c
                where pe.carrera=c.id
                order by pe.fecha desc
            ");

            $query->execute();
            $planes = $query->fetchAll();

            for ($i = 0; $i < count($planes); $i++)
            {
                // Pego la mencion
                $m = array();

                if ($planes[$i]['mencion_id'] != null)
                {
                    $query = $this->db->prepare("
                        select nombre
                        from Mencion
                        where id=:mid
                    ");

                    $query->execute(array(
                        ":mid" => $planes[$i]['mencion_id']
                    ));

                    $m = $query->fetchAll();
                }

                $planes[$i]['mencion'] = count($m) > 0 ? $m[0]['nombre'] : null;

                // Pego la materia
                $m = array();
                
                if ($planes[$i]['materia_id'] != null)
                {
                    $query = $this->db->prepare("
                        select m.nombre as nombre
                        from Materia as m, Car_Per as cp, Carrera as c
                        where m.dictada_en=cp.id and cp.carrera=c.id
                        and c.id=:cid
                    ");

                    $query->execute(array(
                        ":cid" => $planes[$i]['carrera_id']
                    ));

                    $m = $query->fetchAll();
                }

                $planes[$i]['materia'] = count($m) > 0 ? $m[0]['nombre'] : null;
            }

            return json_encode($planes);
        }

        public function cargar_guias($post)
        {
            $query = $this->db->prepare("select * from Lista_Todas where status=:status order by id desc");

            $query->execute(array(
                ":status" => $post["status"]
            ));

            $gs = $query->fetchAll();
            $guias = array();

            foreach ($gs as $g)
            {
                $row = $g;
                // Guardo los ID
                $row["entregada_por_id"] = $row["entregada_por"];
                $row["profesor_id"] = $row["profesor"];
                $row["recibida_por_id"] = $row["recibida_por"];
                $carrera = $this->obtener_carrera_desde_materia($g['materia']);
                $row["carrera_id"] = $carrera['id'];
                $row["carrera_nombre"] = $carrera['nombre'];

                // Reemplazo por los valores de la foranea
                $row["entregada_por"] = $this->cargar_profesor($row["profesor"]);
                $row["profesor"] = $this->cargar_profesor($row["profesor"]);
                $row["recibida_por"] = $this->cargar_personal_($row["recibida_por"]);

                if ($row["status"] == -1)
                    $row["status_str"] = "rechazada";
                else if ($row["status"] == 0)
                    $row["status_str"] = "pendiente";
                else if ($row["status"] == 1)
                    $row["status_str"] = "aprobada";
                else if ($row["status"] == 2)
                    $row["status_str"] = "inactiva";

                $guias[] = $row;
            }

            return json_encode($guias);
        }

        public function cargar_guia($post)
        {
            $query = $this->db->prepare("select * from Lista_Todas where codigo=:codigo order by id desc");

            $query->execute(array(
                ":codigo" => $post["codigo"]
            ));

            $gs = $query->fetchAll();
            $guias = array();

            foreach ($gs as $g)
            {
                $row = $g;
                // Guardo los ID
                $row["entregada_por_id"] = $row["entregada_por"];
                $row["profesor_id"] = $row["profesor"];
                $row["recibida_por_id"] = $row["recibida_por"];
                $carrera = $this->obtener_carrera_desde_materia($g['materia']);
                $row["carrera_id"] = $carrera['id'];
                $row["carrera_nombre"] = $carrera['nombre'];

                // Reemplazo por los valores de la foranea
                $row["entregada_por"] = $this->cargar_profesor($row["profesor"]);
                $row["profesor"] = $this->cargar_profesor($row["profesor"]);
                $row["recibida_por"] = $this->cargar_personal_($row["recibida_por"]);

                if ($row["status"] == -1)
                    $row["status_str"] = "rechazada";
                else if ($row["status"] == 0)
                    $row["status_str"] = "pendiente";
                else if ($row["status"] == 1)
                    $row["status_str"] = "aprobada";
                else if ($row["status"] == 2)
                    $row["status_str"] = "inactiva";

                $guias[] = $row;
            }

            return json_encode($guias[0]);
        }

        public function agregar_guia($post)
        {
            $json = array();

            /*$id_prof = -1;

            if ($post['nuevo_prof'] != null)
            {
                $p = array(
                    "nombre" => $post['nuevo_prof']['nombre'],
                    "apellido" => $post['nuevo_prof']['apellido'],
                    "snombre" => isset($post['nuevo_prof']['snombre']) ? $post['nuevo_prof']['snombre'] : null,
                    "sapellido" => isset($post['nuevo_prof']['sapellido']) ? $post['nuevo_prof']['sapellido'] : null,
                    "cedula" => isset($post['nuevo_prof']['cedula']) ? $post['nuevo_prof']['cedula'] : null,
                    "tlfs" => isset($post['nuevo_prof']['tlfs']) ? $post['nuevo_prof']['tlfs'] : null,
                    "email" => isset($post['nuevo_prof']['email']) ? $post['nuevo_prof']['email'] : null
                );

                $this->agregar_profesor($p);

                $q = $this->db->prepare("select id from Profesor order by id desc limit 1");
                $q->execute();

                $id_prof = $q->fetchAll();
                $id_prof = $id_prof[0]['id'];
            }
            else
                $id_prof = $post['profesor'];

            if ($id_prof == -1)
            {
                echo "Error (id_profesor)";
                return;
            }*/

            $query = $this->db->prepare("
                insert into Guia (titulo, seccion, comentario, profesor, materia, entregada_por, recibida_por, fecha_anadida, tipo)
                values (:titulo, :seccion, :comentario, :profesor, :materia, :entregada_por, :recibida_por, now(), :tipo);
            ");

            $query->execute(array(
                ":titulo" => $post['titulo'],
                ":seccion" => $post['seccion'],
                ":comentario" => isset($post['comentario']) ? $post['comentario'] : null,
                ":profesor" => $post['profesor'],
                ":materia" => $post['materia'],
                ":entregada_por" => $post['recibida_por'],
                ":recibida_por" => $post['recibida_por'],
                ":tipo" => isset($post['tipo']) ? $post['tipo'] : null
            ));

            $json['status'] = "ok";
            $json['id_guia'] = $this->db->lastInsertId();

            // Le asigno el ID al codigo
            $query = $this->db->prepare("
                update Guia set codigo=:id where id=:id
            ");

            $query->execute(array(
                ":id" => $json['id_guia']
            ));

            return json_encode($json);
        }

        public function agregar_profesor($post)
        {
            try 
            {
                $query = $this->db->prepare("call agregar_profesor(:nombre, :snombre, :apellido, :sapellido, :cedula, :tlfs, :email)");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":apellido" => $post['apellido'],
                    ":snombre" => isset($post['snombre']) ? $post['snombre'] : null,
                    ":sapellido" => isset($post['sapellido']) ? $post['sapellido'] : null,
                    ":cedula" => isset($post['cedula']) ? $post['cedula'] : null,
                    ":tlfs" => isset($post['tlfs']) ? $post['tlfs'] : null,
                    ":email" => isset($post['email']) ? $post['email'] : null
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_plan_de_estudio($post)
        {
            $mencion_campo = "";
            $mencion_valor = "";

            if (isset($post['mencion']))
                if ($post['mencion'] != -1)
                {
                    $mencion_campo = ", mencion";
                    $mencion_valor = ", '".$post['mencion']."'";
                }

            $query = $this->db->prepare("
                insert into Plan_de_Estudio (titulo, carrera, materia, tipo, comentario, pdf, paginas, hojas, para, fecha".$mencion_campo.")
                values (:titulo, :carrera, :materia, :tipo, :comentario, :pdf, :paginas, :hojas, :para, now()".$mencion_valor.")
            ");

            $query->execute(array(
                ":titulo" => $post['titulo'],
                ":carrera" => $post['carrera'],
                ":tipo" => $post['tipo'],
                ":comentario" => $post['comentario'],
                ":pdf" => $post['pdf'],
                ":paginas" => $post['paginas'],
                ":hojas" => $post['hojas'],
                ":para" => $post['para'],
                ":materia" => isset($post['materia']) ? $post['materia'] : null
            ));

            return "ok";
        }

        public function agregar_dependencia($post)
        {
            try 
            {
                $query = $this->db->prepare("insert into Dependencia (nombre) values (:nombre)");

                $query->execute(array(
                    ":nombre" => $post['nombre']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_material($post)
        {
            $query = $this->db->prepare("
                insert into Material (nombre) values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_stock($post)
        {
            $query = $this->db->prepare("
                insert into Stock (cantidad, fecha_anadido, costo, material) 
                values (:cantidad, now(), :costo, :material)
            ");

            $query->execute(array(
                ":cantidad" => $post['cantidad'],
                ":costo" => $post['costo'],
                ":material" => $post['material']
            ));

            return "ok";
        }

        public function agregar_carrera($post)
        {
            try 
            {
                $query = $this->db->prepare("call agregar_carrera(:nombre, :tipo)");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":tipo" => $post['tipo']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                insert into Departamento_UCAB (nombre)
                values (:nombre)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            return "ok";
        }

        public function agregar_orden($post)
        {
            @session_start();

            $query = $this->db->prepare("
                insert into Orden (numero, dpto_ucab, dependencia, destino, observaciones, creado_por, fecha_anadida, fecha_modificada)
                values (:numero, :dpto_ucab, :dependencia, :destino, :observaciones, (select id from Personal where usuario=:usuario), now(), now())
            ");

            $query->execute(array(
                ":numero" => $post['numero'],
                ":dpto_ucab" => $post['dpto_ucab'],
                ":dependencia" => $post['dependencia'],
                ":destino" => $post['destino'],
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":usuario" => $_SESSION['login_username']
            ));

            $oid = $this->db->lastInsertId();

            /* Añado las personas autorizadas */
            if (isset($post['personas']))
                foreach ($post['personas'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Persona_Autorizada (orden, nombre_completo, cedula)
                        values (:orden, :nombre, :cedula)
                    ");

                    $query->execute(array(
                        ":orden" => $oid,
                        ":nombre" => $p['nombre'],
                        ":cedula" => $p['cedula']
                    ));
                }

            /* Añado los productos */
            if (isset($post['productos']))
                foreach ($post['productos'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Orden_Producto (orden, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total)
                        values (
                            :orden,
                            :producto,
                            :cantidad,
                            :nro_copias,
                            :nro_originales,
                            (select costo from Producto_Costo where producto=1 and eliminado=0 order by fecha desc limit 1),
                            (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad
                        )
                    ");

                    $query->execute(array(
                        ":orden" => $oid,
                        ":producto" => $p['producto'],
                        ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                        ":nro_copias" => intval($p['nro_copias']),
                        ":nro_originales" => intval($p['nro_originales'])
                    ));
                }

            return "ok";
        }

        public function editar_orden($post)
        {
            $query = $this->db->prepare("
                update Orden set 
                    numero=:numero, 
                    dpto_ucab=:dpto_ucab, 
                    dependencia=:dependencia,
                    destino=:destino,
                    observaciones=:observaciones,
                    fecha_modificada=now()
                where id=:id
            ");

            $query->execute(array(
                ":numero" => $post['numero'],
                ":dpto_ucab" => $post['dpto_ucab'],
                ":dependencia" => $post['dependencia'],
                ":destino" => $post['destino'],
                ":observaciones" => isset($post['observaciones']) ? $post['observaciones'] : null,
                ":id" => $post['id']
            ));

            /* Elimino las personas */
            $query = $this->db->prepare("
                delete from Persona_Autorizada where orden=:orden
            ");

            $query->execute(array(
                ":orden" => $post['id']
            ));

            if (isset($post['personas']))
                foreach ($post['personas'] as $p)
                {
                    $query = $this->db->prepare("
                        insert into Persona_Autorizada (orden, nombre_completo, cedula)
                        values (:orden, :nombre, :cedula)
                    ");

                    $query->execute(array(
                        ":orden" => $post['id'],
                        ":nombre" => $p['nombre'],
                        ":cedula" => $p['cedula']
                    ));
                }

            /* Veo si debo modificar los productos */
            $eliminar = false;

            if (isset($post['productos']))
            {
                foreach ($post['productos'] as $p)
                    if (!isset($p['costo_unitario_facturado']))
                        $eliminar = true;
            }
            else
                $eliminar = true;

            if ($eliminar)
            {
                /* Elimino los productos */
                $query = $this->db->prepare("
                    delete from Orden_Producto where orden=:orden
                ");

                $query->execute(array(
                    ":orden" => $post['id']
                ));

                /* Añado los productos */
                if (isset($post['productos']))
                    foreach ($post['productos'] as $p)
                    {
                        $query = $this->db->prepare("
                            insert into Orden_Producto (orden, producto, cantidad, nro_copias, nro_originales, precio_unitario, precio_total)
                            values (
                                :orden,
                                :producto,
                                :cantidad,
                                :nro_copias,
                                :nro_originales,
                                (select costo from Producto_Costo where producto=1 and eliminado=0 order by fecha desc limit 1),
                                (select costo from Producto_Costo where producto=:producto and eliminado=0 order by fecha desc limit 1) * :cantidad
                            )
                        ");

                        $query->execute(array(
                            ":orden" => $post['id'],
                            ":producto" => $p['producto'],
                            ":cantidad" => intval($p['nro_copias']) * intval($p['nro_originales']),
                            ":nro_copias" => intval($p['nro_copias']),
                            ":nro_originales" => intval($p['nro_originales'])
                        ));
                    }
            }

            return "ok";
        }

        public function agregar_materia($post)
        {
            $query = $this->db->prepare("
                insert into Materia (nombre, tipo, dictada_en) 
                values (:nombre, :tipo, :periodo)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":periodo" => $post['periodo'],
                ":tipo" => $post['tipo']
            ));

            return "ok";
        }

        public function agregar_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                insert into CuentaAbierta (nombre, vence)
                values (:nombre, :vence)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":vence" => isset($post['vence_']) ? $post['vence_'] : null
            ));

            return "ok";
        }

        public function agregar_producto($post)
        {
            $query = $this->db->prepare("
                insert into Producto (nombre, descripcion, departamento, fecha_creado)
                values (:nombre, :descripcion, :departamento, now())
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":descripcion" => $post['descripcion'],
                ":departamento" => $post['departamento']
            ));

            $pid = $this->db->lastInsertId();

            /* Agrego el costo */
            $query = $this->db->prepare("
                insert into Producto_Costo (producto, costo, fecha)
                values (:pid, :costo, now())
            ");

            $query->execute(array(
                ":pid" => $pid,
                ":costo" => $post['costo']
            ));

            /* Agrego los materiales */
            if (isset($post['materiales']))
                foreach ($post['materiales'] as $m)
                {
                    $query = $this->db->prepare("
                        insert into Producto_Material (producto, material, cantidad, creado_por, fecha_creado)
                        values (:producto, :material, :cantidad, :creado_por, now())
                    ");

                    $query->execute(array(
                        ":producto" => $pid,
                        ":material" => $m['material'],
                        ":cantidad" => $m['cantidad'],
                        ":creado_por" => $_SESSION['login_username']
                    ));
                }

            return "ok";
        }

        public function agregar_mencion($post)
        {
            $query = $this->db->prepare("
                insert into Mencion (nombre, carrera) 
                values (:nombre, :carrera)
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":carrera" => $post['carrera']
            ));

            return "ok";
        }

        public function editar_mencion($post)
        {
            $query = $this->db->prepare("
                update Mencion set 
                    nombre=:nombre, 
                    carrera=:carrera
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":carrera" => $post['cid'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_producto($post)
        {
            $query = $this->db->prepare("
                update Producto set 
                    nombre=:nombre, 
                    descripcion=:descripcion,
                    departamento=:departamento
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":descripcion" => $post['descripcion'],
                ":departamento" => $post['departamento'],
                ":id" => $post['id']
            ));

            // Veo si tiene nuevo precio
            if (isset($post['costo_nuevo']))
            {
                $query = $this->db->prepare("
                    insert into Producto_Costo (producto, costo, fecha)
                    values (:pid, :costo, now())
                ");

                $query->execute(array(
                    ":pid" => $post['id'],
                    ":costo" => $post['costo_nuevo']
                ));
            }

            /* Elimino los materiales */
            $query = $this->db->prepare("
                delete from Producto_Material where producto=:pid
            ");

            $query->execute(array(
                ":pid" => $post['id']
            ));

            /* Agrego los materiales */
            if (isset($post['materiales']))
                foreach ($post['materiales'] as $m)
                {
                    $query = $this->db->prepare("
                        insert into Producto_Material (producto, material, cantidad, creado_por, fecha_creado)
                        values (:producto, :material, :cantidad, :creado_por, now())
                    ");

                    $query->execute(array(
                        ":producto" => $post['id'],
                        ":material" => $m['material'],
                        ":cantidad" => $m['cantidad'],
                        ":creado_por" => $_SESSION['login_username']
                    ));
                }

            return "ok";
        }

        public function editar_cuentaabierta($post)
        {
            $query = $this->db->prepare("
                update CuentaAbierta set 
                    nombre=:nombre, 
                    vence=:vence
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":vence" => isset($post['vence_']) ? $post['vence_'] : null,
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_departamento_ucab($post)
        {
            $query = $this->db->prepare("
                update Departamento_UCAB set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_dependencia($post)
        {
            $query = $this->db->prepare("
                update Dependencia set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_material($post)
        {
            $query = $this->db->prepare("
                update Material set 
                    nombre=:nombre
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));

            return "ok";
        }

        public function editar_materia($post)
        {
            try 
            {
                $query = $this->db->prepare("
                    update Materia set 
                        nombre=:nombre,
                        tipo=:tipo, 
                        dictada_en=:periodo
                    where id=:id
                ");

                $query->execute(array(
                    ":nombre" => $post['nombre'],
                    ":periodo" => $post['periodo_id'],
                    ":tipo" => $post['tipo_id'],
                    ":id" => $post['id']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function agregar_personal($post)
        {
            $query = $this->db->prepare("
                insert into Personal (nombre, segundo_nombre, apellido, segundo_apellido, cedula, telefono, email, usuario, contrasena, fecha_creado)
                values (:nombre, :snombre, :apellido, :sapellido, :cedula, :telefono, :email, :usuario, :contrasena, now())
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":apellido" => $post['apellido'],
                ":snombre" => $post['snombre'],
                ":sapellido" => $post['sapellido'],
                ":cedula" => $post['cedula'],
                ":telefono" => $post['telefono'],
                ":email" => $post['email'],
                ":usuario" => $post['usuario'],
                ":contrasena" => $post['contrasena']
            ));

            $uid = $this->db->lastInsertId();

            // Añado los permisos
            if (isset($post['permisos']))
            {
                $permisos = explode("]", $post['permisos']);

                foreach ($permisos as $p_)
                {
                    $p = str_replace("[", "", $p_);

                    if (strlen($p) == 0) continue;

                    $query = $this->db->prepare("
                        insert into Permiso_Asignado (permiso, usuario)
                        values (:pid, :uid)
                    ");

                    $query->execute(array(
                        ":pid" => $p,
                        ":uid" => $uid
                    ));
                }
            }

            return "ok";
        }

        public function editar_carrera($post)
        {
            $query = $this->db->prepare("
                update Carrera set nombre=:nombre where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":id" => $post['id']
            ));
        }

        public function editar_profesor($post)
        {
            $query = $this->db->prepare("
                update Profesor set 
                    nombre=:nombre,
                    segundo_nombre=:segundo_nombre,
                    apellido=:apellido,
                    segundo_apellido=:segundo_apellido,
                    email=:email,
                    cedula=:cedula,
                    telefono=:telefono
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":segundo_nombre" => $post['segundo_nombre'],
                ":apellido" => $post['apellido'],
                ":segundo_apellido" => $post['segundo_apellido'],
                ":email" => $post['email'],
                ":cedula" => $post['cedula'],
                ":telefono" => $post['telefono'],
                ":id" => $post['id']
            ));
        }

        public function editar_personal($post)
        {
            $query = $this->db->prepare("
                update Personal set 
                    nombre=:nombre,
                    segundo_nombre=:snombre,
                    apellido=:apellido,
                    segundo_apellido=:sapellido,
                    cedula=:cedula,
                    telefono=:telefono,
                    email=:email,
                    usuario=:usuario,
                    contrasena=:contrasena
                where id=:id
            ");

            $query->execute(array(
                ":nombre" => $post['nombre'],
                ":apellido" => $post['apellido'],
                ":snombre" => isset($post['snombre']) ? $post['snombre'] : null,
                ":sapellido" => isset($post['sapellido']) ? $post['sapellido'] : null,
                ":cedula" => isset($post['cedula']) ? $post['cedula'] : null,
                ":telefono" => isset($post['telefono']) ? $post['telefono'] : null,
                ":email" => isset($post['email']) ? $post['email'] : null,
                ":usuario" => $post['usuario'],
                ":contrasena" => $post['contrasena'],
                ":id" => $post['id']
            ));

            // Borro los permisos
            $query = $this->db->prepare("
                delete from Permiso_Asignado where usuario=:uid
            ");

            $query->execute(array(
                ":uid" => $post['id']
            ));

            // Añado los permisos
            $permisos = explode("]", $post['permisos']);

            foreach ($permisos as $p_)
            {
                $p = str_replace("[", "", $p_);

                if (strlen($p) == 0) continue;

                $query = $this->db->prepare("
                    insert into Permiso_Asignado (permiso, usuario)
                    values (:pid, :uid)
                ");

                $query->execute(array(
                    ":pid" => $p,
                    ":uid" => $post['id']
                ));
            }

            return "ok";
        }

        public function eliminar_pdf($pdf)
        {
            $query = $this->db->prepare("
                update Guia set pdf=null where pdf=:pdf
            ");

            $query->execute(array(
                ":pdf" => $pdf
            ));
        }

        public function cambiar_estado($post)
        {
            $query = $this->db->prepare("
                call cambiar_estado_guia(:status, :codigo);
            ");

            $query->execute(array(
                ":status" => $post['status'],
                ":codigo" => $post['codigo']
            ));

            return "ok";
        }

        public function modificar_guia($post)
        {
            try 
            {
                $query = $this->db->prepare("call modificar_guia(:codigo, :titulo, :seccion, :comentario, :pdf, :profesor, :materia, :entregada_por, :recibida_por, :nro_hojas, :nro_paginas)");

                $query->execute(array(
                    ":codigo" => $post['codigo'],
                    ":titulo" => $post['titulo'],
                    ":seccion" => $post['seccion'],
                    ":comentario" => isset($post['comentario']) ? $post['comentario'] : null,
                    ":pdf" => $post['pdf'],
                    ":profesor" => $post['profesor'],
                    ":materia" => $post['materia'],
                    ":entregada_por" => $post['entregada_por'],
                    ":recibida_por" => $post['recibida_por'],
                    ":nro_hojas" => $post['hojas'],
                    ":nro_paginas" => $post['paginas']
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function registrar_vista_guia($username, $archivo, $resultado, $errores)
        {
            try 
            {
                $query = $this->db->prepare("
                    insert into Log_Vista_Guias (fecha, username, resultado, errores, archivo)
                    values (now(), :username, :resultado, :errores, :archivo)
                ");

                $query->execute(array(
                    ":username" => $username,
                    ":resultado" => $resultado,
                    ":errores" => $errores,
                    ":archivo" => $archivo
                ));

                return "ok";
            }
            catch (Exception $e)
            {
                return "error";
            }
        }

        public function puede_ver_guias($username)
        {
            $query = $this->db->prepare("
                select *
                from Personal as p, Permiso_Asignado as pa, Permiso as pe
                where pa.usuario=p.id and pa.permiso=pe.id
                and p.usuario=:username and pe.nombre='buscar_guias'
            ");

            $query->execute(array(
                ":username" => $username
            ));

            return $query->rowCount() > 0;
        }

        public function check_nro_orden($post)
        {
            $query = $this->db->prepare("
                select *
                from Orden
                where numero=:nro
            ");

            $query->execute(array(
                ":nro" => $post['nro']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_usuario($post)
        {
            $query = $this->db->prepare("
                select *
                from Personal
                where usuario=:username
            ");

            $query->execute(array(
                ":username" => $post['username']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }

        public function check_producto($post)
        {
            $query = $this->db->prepare("
                select *
                from Producto
                where nombre=:nombre
            ");

            $query->execute(array(
                ":nombre" => $post['nombre']
            ));

            $json = array();
            $json['existe'] = $query->rowCount() > 0 ? true : false;
            $json['esValido'] = $query->rowCount() == 0 ? true : false;

            return json_encode($json);
        }
        
	}
?>