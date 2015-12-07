<?php
namespace Controller;

class Story extends Base
{

	public function __construct()
	{
		$this->model = \Model\Story::instance();
		//$this->view = new \View\Story;
	}

	public function index(\Base $f3, $params)
	{
		switch($params['action'])
		{
			case 'read':
				$data = $this->read($params['id']);
				break;
			case 'print':
				$this->printer($params['id']);
				break;
				
		}
		$this->buffer ($data);
	}
	
	protected function printer($id)
	{
		$id = explode(",",$id);
		$printer = ($id[1]=="") ? "paper" : $id[1];
		$id = $id[0];
		
		if ( $printer == "epub" ) $this->model->printEPub($id);

	}


	protected function read($id)
	{
		$id = explode(",",$id);

		if($storyData = $this->model->getStory($id[0]))
		{
			$story = $id[0];
			if ( empty($id[1]) AND $storyData['chapters']>1 ) $id[1] = "toc";

			if ( isset($id[1]) AND $id[1] == "reviews" )
			{
				$tocData = $this->model->getMiniTOC($story);
				$content = "";
			}
			elseif ( isset($id[1]) AND $id[1] == "toc" AND $storyData['chapters']>1 )
			{
				$tocData = $this->model->getTOC($story);
				$content = \View\Story::buildTOC($tocData,$storyData);
			}
			else
			{
				if( empty($id[1]) OR !is_numeric($id[1]) ) $id[1] = 1;
				$chapter = $id[1] = max ( 1, min ( $id[1], $storyData['chapters']) );
				$tocData = $this->model->getMiniTOC($story);
				\Base::instance()->set('bigscreen',TRUE);
				$content = ($content = $this->model->getChapter( $story, $chapter )) ? : "Error";
			}

			$dropdown = \View\Story::dropdown($tocData,$id[1]);
			$view = \View\Story::buildStory($storyData,$content,$dropdown);
			$this->buffer($view);
		}
		else $this->buffer("Error, not found");
		/*
		if( isset($id[1]) AND is_numeric($id[1]) )
		{
			$data = $this->model->getStory($id);
		}
		else
		{
			$data = $this->model->getTOC($id[0]);
		}
		return ( $data );
		*/
	}
	
}