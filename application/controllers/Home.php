<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class Home extends CI_Controller {

	public function __construct()
    {
        parent::__construct();
        $this->load->model('Denah_model');
    }

	public function index()
	{
		$this->load->view('page/index');
	}

	public function inputDenah(){
		$ids = $this->input->post('id');
		$defaultColor = "#e6e7e8";
		if(is_array($ids)){
			foreach($ids as $id){                
				$denah = new Denah_model;
				$denah->code = $id;
				$denah->description = '';
				$denah->type = 'default';
				$denah->color = $defaultColor;
				$denah->save();
			}
		}
	}

	public function allDenahColor(){
		$ids = Denah_model::all();
		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode([
				'message' => '',
				'results' => $ids->toArray(),
			]));
	}

    function change_denah(){
        // $this->load_db_connection();
        $code = $this->input->post('code');
        $type = $this->input->post('type');
        $desc = $this->input->post('desc');

        $color = '#e6e7e8';
        if($type == 'Sudah DP'){
            $color = 'yellow';
        }elseif ($type == 'Laku Terjual'){
            $color = 'green';
        }

        $denah = Denah_model::where('code', $code)
        ->update(['type' => $type, 'description' => $desc, 'color' => $color]);
        return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode([
				'message' => '',
				'results' => [
                    'code' => $code,
                    'color' => $color,
                ],
			]));
    }

	function search(){
        $draw = $this->input->get('draw');
        $start = ($this->input->get('start') != null) ? $this->input->get('start') : 0;
        $rowperpage = ($this->input->get('length') != null) ? $this->input->get('length') : 10;
        $order = ($this->input->get('order') != null) ? $this->input->get('order') : false;
        $search = ($this->input->get('search') != null && $this->input->get('search')['value'] != null) ? $this->input->get('search') : false;

        $model = new Denah_model;

        $totalRows = $model->count();
        $filteredRows = $totalRows;

        if ($search) {
			$search = $search['value'];
            $model = $model->where('code', 'LIKE', '%'.$search.'%')
            ->orWhere('description', 'LIKE', '%'.$search.'%')
            ->orWhere('type', 'LIKE', '%'.$search.'%')
            ->orWhere('color', 'LIKE', '%'.$search.'%');
            $filteredRows = $model->count();
        }

        $model = $model->skip((int) $start);
        $model = $model->take((int) $rowperpage);

        if($order){
            foreach($this->input->get('columns') as $key => $column){
                $direction = ($order[0]['dir'] == 'asc') ? 'ASC' : 'DESC';
                if($key == $order[0]['column']){
                    $model = $model->orderBy($column['name'], $direction);
                }
            }
        }
        
        $resuls = $model->select('denahs.*')->get();

        $data_arr = [];

        foreach($resuls as $result){
            $data_arr[] = [
                'code' => $result->code,
                'description' => $result->description,
                'type' => $result->type,
                'color' => '<span class="pup" style="background-color:'.$result->color.'"></span> '.$result->color,
				'action' => '<button onclick="openDataRow(\''.$result->code.'\', \''.$result->type.'\', \''.$result->description.'\')" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal"><i class="fa fa-edit"></i> Edit</button>',
            ];
        }

        // return [
        //     'draw'            => $draw,
        //     'recordsTotal'    => $totalRows,
        //     'recordsFiltered' => $filteredRows,
        //     'data'            => $data_arr,
        // ];

		return $this->output
			->set_content_type('application/json')
			->set_status_header(200)
			->set_output(json_encode([
				'draw'            => $draw,
            	'recordsTotal'    => $totalRows,
            	'recordsFiltered' => $filteredRows,
            	'data'            => $data_arr,
			]));
    }

}
