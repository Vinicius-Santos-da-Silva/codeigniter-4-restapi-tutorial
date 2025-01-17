<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BlogHasTopicoModel;
use App\Models\UsuarioModel;
use App\Helpers\User;

class Blog extends ResourceController
{
	protected $modelName = 'App\Models\BlogModel';
	protected $format = 'json';

	public function index(){
		$limit = $this->request->getVar("limit");
		$offset = $this->request->getVar("offset");

		if(!$limit) {
			$limit = 10;
		}
		
		if(!$offset) {
			$offset = 0;
		}

		$posts = $this->model->findAll($limit , $offset);
		
		$all_results = $this->model->countAllResults();

		$to = $offset+$limit;
		
		if($to > $all_results) {
			$to = $to + ($all_results - $to);
		}
		
		$response = array(
			'data' => $posts,
			'total_pages' => ceil($all_results/$limit),
			'total' => $all_results,
			'from' => $offset+1,
			'to' => $to,
			'limit' => 10,
		);

		return $this->respond($response);
	}

	public function create(){
		helper(['form']);

		$rules = [
			'title' => 'required|min_length[6]',
			'description' => 'required',
		];

		if(!$this->validate($rules)){
			return $this->fail($this->validator->getErrors());
		}

		$data = [
			'post_title' => $this->request->getVar('title'),
			'post_description' => $this->request->getVar('description'),
		];

		$post_id = $this->model->insert($data);
		
		$data['post_id'] = $post_id;

		return $this->respondCreated($data);
		
	}

	public function show($id = null){
		
	
		$data = $this->model->find($id);

		if(!$data) {
			return $this->failNotFound('Item not found');
		}

		$usuario = User::getFromRequest();
		
		if(!$usuario->isPremium() && $data->isPremium()) {
			return $this->failUnauthorized();
		}

		$data->findPosts();
		$data->findTopicoPai();

		return $this->respond($data);

	}

	public function update($id = null){

		helper(['form', 'array']);

		$rules = [
			'title' => 'required|min_length[6]',
			'slug' => 'required|min_length[3]',
			'description' => 'required',
			'is_free' => 'required',
		];


		$fileName = dot_array_search('featured_image.name', $_FILES);

		if($fileName != ''){
			$img = ['featured_images' => 'uploaded[featured_image]|max_size[featured_image, 1024]|is_image[featured_image]'];
			$rules = array_merge($rules, $img);
		}



		if(!$this->validate($rules)){
			return $this->fail($this->validator->getErrors());
		}
			
		$data = [
			'post_id' => $id,
			'post_title' => $this->request->getVar('title'),
			'post_description' => $this->request->getVar('description'),
			'is_free' => $this->request->getVar('is_free'),
			'slug' => $this->request->getVar('slug'),
		];

		
		
		
		$topico_id = $this->request->getVar('topico');

		if($topico_id) {
			$blog_topico_model = new BlogHasTopicoModel(); 

			$blog_topico_ = $blog_topico_model->where(['blog_post_id' => $id])->delete();

			$data_insert = [
				'topico_id' => $topico_id,
				'blog_post_id' => $id
			];
			
			$uuid = $blog_topico_model->insert($data_insert);
		}
		
		

		$this->model->save($data);

		return $this->respond($data);

	}

	public function delete($id = null){
		$data = $this->model->find($id);
		if($data){
			$this->model->delete($id);
			return $this->respondDeleted($data);
		}else{
			return $this->failNotFound('Item not found');
		}
	}



	//--------------------------------------------------------------------

}
