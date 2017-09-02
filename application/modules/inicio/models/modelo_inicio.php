<?php
class Modelo_inicio extends CI_Model {
	private $tabla;
	private $prefijo;
	private $foreign;
	
	function __construct(){
		/* 
			El nombre de la tabla debe ser igual al nombre del modelo
			por ejemplo, si la tabla se llama "empresa", el modelo se
			deberia llamar "Modelo_empresa";
		 */
		$this->tabla = str_replace("Modelo_", "", get_class());
		/* 
			El prefijo se calcula a partir del nombre de la tabla, si la tabla
			tiene un nombre de una palabra, toma las primeras 2 letras, por
			ejemplo: empresa = em
			Si tiene un nombre de 2 letras, toma la primera letra de cada palabra,
			por ejemplo: estudio_factibilidad = ef
			Para otro tipo de prefijo, cambiar manualmente o dejar como
			string vacio
		*/
		$this->prefijo = $this->prefijo();
		/* 
			$this->foreign se deja como array vacio si la tabla no tiene relacion 
			con otra, sino, dejar la key como el nombre del campo, 
			y el value como el nombre de la tabla, 
			por ejemplo: ["co_codigo" => "comuna"] 
		*/
		$this->foreign = [];
		parent::__construct();
	}

	private function prefijo(){
		if(count(explode("_", $this->tabla))>1){
			return substr(explode("_", $this->tabla)[0], 0, 1) . substr(explode("_", $this->tabla)[1], 0, 1) . "_";
		}else{
			return substr($this->tabla, 0, 2) . "_";
		}
	}
	
	public function getLastId(){
		$this->db->select_max("{$this->prefijo}codigo","maximo");
		$sql = $this->db->get($this->tabla);
		return $sql->row()->maximo+1;
	}
	
	public function insertar($datos){
		return $this->db->insert($this->tabla, $datos);
	}
	
	public function actualizar($datos, $where){
		$this->db->where($where);
		return $this->db->update($this->tabla, $datos);
	}

	public function eliminar($datos, $where){
		$this->db->where($where);
		return $this->db->delete($this->tabla);
	}

	public function obtener_por_codigo($codigo){
		$sql = $this->db->select('*')
				->from($this->tabla)
				->where("{$this->prefijo}codigo", $codigo)
				->limit(1)
				->get();
				
        $resultado = $sql->row();
		
        if($resultado){
			$obj = new stdClass();
			foreach(get_object_vars($resultado) as $key => $val){
				$obj->{str_replace($this->prefijo, "", $key)} = $resultado->{$key};
			}
			return $obj;
        }else{
			return false;
        }
	}
	
	public function obtener($where){
		$sql = $this->db->select('*')
				->from($this->tabla)
				->where($where)
				->limit(1)
				->get();
				
        $resultado = $sql->row();
		
        if($resultado){
			$obj = new stdClass();
			foreach(get_object_vars($resultado) as $key => $val){
				$obj->{str_replace($this->prefijo, "", $key)} = $resultado->{$key};
			}
			return $obj;
        }else{
			return false;
        }
	}
	
	public function listar($where = false, $pagina = false, $cantidad = false){

		if($pagina && $cantidad){
			$desde = ($pagina - 1) * $cantidad;
			$this->db->limit($cantidad, $desde);
		}

		if($cantidad){
			$this->db->limit($cantidad);
		}

		if($where) $this->db->where($where);
		$sql = $this->db->select('*')
				->from($this->tabla)
				->get();
        $result = $sql->result();
        if($result){
			$listado = array();
			foreach($result as $resultado){
				$obj = new stdClass();
				foreach(get_object_vars($resultado) as $key => $val){
					if(in_array(str_replace($this->prefijo, "", $key), array_keys($this->foreign))){
						$tabla = $this->foreign[str_replace($this->prefijo, "", $key)];
						$this->load->model("modelo_{$tabla}", "objRel");
						$obj->{$tabla} = $this->objRel->obtener_por_codigo($resultado->{$key});
					}else{
						$obj->{str_replace($this->prefijo, "", $key)} = $resultado->{$key};
					}
				}
				$listado[] = $obj;
			}
			return $listado;
        }else {
			return false;
        }
    }
}