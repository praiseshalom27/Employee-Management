<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Staff extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        if ( ! $this->session->userdata('logged_in'))
        { 
            redirect(base_url().'login');
        }
    }

    public function index()
    {
        $data['department']=$this->Department_model->select_departments();
        $data['country']=$this->Home_model->select_countries();
        $this->load->view('admin/header');
        $this->load->view('admin/add-staff',$data);
        $this->load->view('admin/footer');
    }
	 


    public function manage()
    {
        $data['content']=$this->Staff_model->select_staff();
        $this->load->view('admin/header');
        $this->load->view('admin/manage-staff',$data);
        $this->load->view('admin/footer');
    }


    public function insert()
    {
        $this->form_validation->set_rules('txtname', 'Full Name', 'required');
        $this->form_validation->set_rules('slcdepartment', 'Department', 'required');
	    $this->form_validation->set_rules('age', 'Age', 'required');
        $this->form_validation->set_rules('experience', 'Experience', 'required');
        $name=$this->input->post('txtname');
        $department=$this->input->post('slcdepartment');
        $age=$this->input->post('age');
        $experience=$this->input->post('experience');
        $added=$this->session->userdata('userid');

        if($this->form_validation->run() !== false)
        {
            $this->load->library('image_lib');
            $config['upload_path']= 'uploads/profile-pic/';
            $config['allowed_types'] ='csv';
            $this->load->library('upload', $config);
            
              $data=$this->Staff_model->insert_staff(array('staff_name'=>$name,'age'=>$age,'experience'=>$experience,'department_id'=>$department));
            
            
            if($data==true)
            {
                
                $this->session->set_flashdata('success', "New Employee Added Succesfully"); 
            }else{
                $this->session->set_flashdata('error', "Sorry, New Employee Adding Failed.");
            }
            redirect($_SERVER['HTTP_REFERER']);
        }
        else{
            $this->index();
            return false;

        } 
    }

    public function update()
    {
        $this->load->helper('form');
        $this->form_validation->set_rules('txtname', 'Full Name', 'required');
        
        $this->form_validation->set_rules('slcdepartment', 'Department', 'required');
     
        $this->form_validation->set_rules('experience', 'Experience', 'required');
        $this->form_validation->set_rules('age', 'Age', 'required');
     
        
        $id=$this->input->post('txtid');
        $name=$this->input->post('txtname');
        $department=$this->input->post('slcdepartment');
        $age=$this->input->post('age');
        $experience=$this->input->post('experience');
        if($this->form_validation->run() !== false)
        {
			
          
           $data=$this->Staff_model->update_staff(array('staff_name'=>$name,'age'=>$age,'experience'=>$experience,'department_id'=>$department),$id);
            
           if($this->db->affected_rows() > 0)
            {
                $this->session->set_flashdata('success', "Employee Updated Succesfully"); 
            }else{
                $this->session->set_flashdata('error', "Sorry, Employee Updated Failed.");
            }
            redirect(base_url()."manage-staff");
        }
        else{
            $this->index();
            return false;

        } 
    }


    function edit($id)
    {
        $data['department']=$this->Department_model->select_departments();
        $data['content']=$this->Staff_model->select_staff_byID($id);
        $this->load->view('admin/header');
        $this->load->view('admin/edit-staff',$data);
        $this->load->view('admin/footer');
    }
function csvfile()
    {
		$this->load->view('admin/header');
        $this->load->view('admin/add-csv');
        $this->load->view('admin/footer');
    }

    function delete($id)
    {
        $this->Home_model->delete_login_byID($id);
        $data=$this->Staff_model->delete_staff($id);
        if($this->db->affected_rows() > 0)
        {
            $this->session->set_flashdata('success', "Employee Deleted Succesfully"); 
        }else{
            $this->session->set_flashdata('error', "Sorry, Employee Delete Failed.");
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

     public function csv()
    {
		 
            $this->load->library('image_lib');
            $config['upload_path']= 'uploads/profile-pic/';
            $config['allowed_types'] ='csv';
            $this->load->library('upload', $config);
            if ( ! $this->upload->do_upload('filephoto'))
            {
                $image='default-pic.jpg';
            }
            else
            {
                $image_data =   $this->upload->data();

                $configer =  array(
                  'image_library'   => 'gd2',
                  'source_image'    =>  $image_data['full_path'],
                  'maintain_ratio'  =>  TRUE,
                  'width'           =>  150,
                  'height'          =>  150,
                  'quality'         =>  50
                );
                $this->image_lib->clear();
                $this->image_lib->initialize($configer);
                $this->image_lib->resize();
                
                $image=$image_data['file_name'];
            }
           
                $data=$this->Staff_model->csv(array('pic'=>$image));
           
            
            if($data==true)
            {
                
                $this->session->set_flashdata('success', "Csv Added Succesfully"); 
            }else{
                $this->session->set_flashdata('error', "Sorry, Csv Adding Failed.");
            }
            redirect($_SERVER['HTTP_REFERER']);
			
			
			if($_FILES['excelDoc']['name']) {
    $arrFileName = explode('.', $_FILES['excelDoc']['name']);
    if ($arrFileName[1] == 'csv') {
        $handle = fopen($_FILES['excelDoc']['tmp_name'], "r");
        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $count++;
            if ($count == 1) {
                continue; // skip the heading header of sheet
            }
                $name = $connection->real_escape_string($data[0]);
                $age = $connection->real_escape_string($data[1]);
                $experience = $connection->real_escape_string($data[2]);
				$department = $connection->real_escape_string($data[3]);
                $common = new Common();
                $SheetUpload = $common->uploadData($connection,$name,$mobile,$email);
        }
        if ($SheetUpload){
            echo "<script>alert('Excel file has been uploaded successfully !');window.location.href='index.php';</script>";
        }
    }
}
   
			
			
       
    }
	
	public function exportToCSV()
    {
        $filename = 'csv_file.csv';
        // header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/csv; ");
        // get data
       
        $file = fopen('php://output', 'w');
        
          $header = array("Sl.no","Employee Name","Department"," Age","Experience");
          fputcsv($file, $header);
       
       

        $sl_no=1;
        foreach ($usersData as $key=>$line) {
          array_unshift($line,$sl_no);
            fputcsv($file, $line);
            $sl_no++;
        }
        fclose($file);
        exit;
    }

}
//Csv preview
  /*  function csvPreview()
   {
    $.ajax({
     url:base_url+"excel_import/fetch",
     method:"POST",
     success:function(data){
      $('#csv-preview').html(data);
     }
    })
   } */