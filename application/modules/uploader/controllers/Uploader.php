<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by Console.
 * User: Amine Maagoul
 * Date: {date}
 * Time: {time}
 */

class Uploader extends MY_Controller {


    public static $nbr_request  = 0;

    public function __construct(){
        parent::__construct();

        //load model
        $this->load->model("uploader_model");
        $this->load->library('session');
        $this->load->helper('uploader');
        $this->load->helper('images');


    }

    public function onLoaded()
    {
        parent::onLoaded(); // TODO: Change the autogenerated stub


    }


    public function cron(){

        $this->uploader_model->clear();

    }


    public function plugin($data){

        //calculate number of request to skip re-load js libs & css
        self::$nbr_request++;

        $data['rand'] = rand(0,100);
        $data['tag'] = md5(rand(100,200));


        if(!isset($data['array_name'])){
            $data['array_name'] = 'var_'.md5(rand(0,100));
        }

        $this->setUploaderSession($data['limit_key'], $data['limit']);


        if(!isset($data['cache'])){
            $this->setUploaderSession('saved-' . $data['limit_key'], 0);
            $this->setUploaderSession('loaded-images', array());
        }else{

            $data['cache'] = $this->checkAvailabilityArrayDATA($data['cache']);

            $this->setUploaderSession('saved-' . $data['limit_key'], count($data['cache']));
            $saved = array();
            foreach ($data['cache'] as $image){
                if(isset($image['name']))
                    $saved[] = $image['name'];
            }

        }

        if(!isset($data['template'])){
            $html = $this->load->view('plugin/html',$data,TRUE);
        }else{
            $html = $this->load->view($data['template'],$data,TRUE);
        }

        $script = $this->load->view('plugin/script',$data,TRUE);
        $style = $this->load->view('plugin/style',$data,TRUE);


        return array(
            'html' => $html,
            'style' => $style,
            'script' => $script,
            'upload_urls_function' => 'getImages'.$data['rand'],
            'clear_gallery_function' => 'clearGallery'.$data['rand'],
            'var' => $data['array_name'],
        );

    }

    public function setUploaderSession($key,$value){

        $uploader = $this->session->userdata('uploader');

        if(empty($uploader)){
            $uploader = array();
        }

        $uploader[$key] = $value;

        $this->session->set_userdata(array(
            'uploader' => $uploader
        ));

        return $key;
    }

    public function getUploaderSession($key){

        $uploader = $this->session->userdata('uploader');
        if(isset($uploader[$key])){
            return $uploader[$key];
        }

        return array();
    }


    /*
     *
     $upload_plug = $this->uploader->plugin(array(
                                    "limit_key"     => "publishFiles",
                                    "token_key"     => "SzYjES-4555",
                                    "array_name"    => "fileUploaded",
                                    "limit"         => MAX_FILES_UPLOADS,
                                    "cache"         => $images //<= for edit or cached files
                                ));

                                echo $upload_plug['html'];

                                TemplateManager::addScript($upload_plug['script']);

     */


    public function checkAvailabilityID($id=""){

        $image = _openDir($id);

        if(isset($image['name']))
            return TRUE;

        return FALSE;
    }

    public function checkAvailabilityArray($array=array()){

        $data = array();

        foreach ($array as $id){
            $image = _openDir($id);
            if(isset($image['name'])){
                $data[] = $id;
            }
        }

        return $data;
    }

    public function checkAvailabilityArrayDATA($array=array()){

        $data = array();

        foreach ($array as $image){
            if(isset($image['name'])){
                $data[] = $image;
            }
        }

        return $data;
    }

}

/* End of file UploaderDB.php */