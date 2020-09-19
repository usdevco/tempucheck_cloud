<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Applicants_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get applicant
     * @param  string $id Optional - applicantid
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*');

        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('tblapplicants.id', $id);
            $applicant = $this->db->get('tblapplicants')->row();
            
            return $applicant;
        }

        return $this->db->get('tblapplicants')->result_array();
    }

        /**
     * Add new applicant to database
     * @param mixed $data applicant data
     * @return mixed false || applicantid
     */
    public function add($data)
    {
        $data['created_at']   = date('Y-m-d H:i:s');
		$data['updated_at']   = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();
		foreach($data['settings'] as $key => $setting) {
			$data[$key]   = $setting;
		}
		
		unset($data['settings']);

        
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
		
		if(isset($data['applicant_setting_preffered_time_from'])){
			$data['preffered_time']=$data['applicant_setting_preffered_time_from'].'--'.$data['applicant_setting_preffered_time_to'];
			unset($data['applicant_setting_preffered_time_from']);
			unset($data['applicant_setting_preffered_time_to']);
		}else{
			$data['preffered_time']='';
			unset($data['applicant_setting_preffered_time_from']);
			unset($data['applicant_setting_preffered_time_to']);
		}

        $this->db->insert('tblapplicants', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Applicant Added [ID: ' . $insert_id . ']');
            return $insert_id;
        }

        return false;
    }

    /**
     * Update applicant
     * @param  array $data applicant data
     * @param  mixed $id   applicantid
     * @return boolean
     */
    public function update($data, $id)
    {
        $current_applicant_data = $this->get($id);
        
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }		
		if(isset($data['applicant_setting_preffered_time_from'])){
			$data['preffered_time']=$data['applicant_setting_preffered_time_from'].'--'.$data['applicant_setting_preffered_time_to'];
			unset($data['applicant_setting_preffered_time_from']);
			unset($data['applicant_setting_preffered_time_to']);
		}else{
			$data['preffered_time']='';
			unset($data['applicant_setting_preffered_time_from']);
			unset($data['applicant_setting_preffered_time_to']);
		}		
		unset($data['upload_resume']);
		unset($data['upload_dl']);      
        //echo "model".$id; print_r($data); die;
        $this->db->where('id', $id); 
        $this->db->update('tblapplicants', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            logActivity('Applicant Updated [ID: ' . $id . ']');

            return true;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Delete applicant from database and all connections
     * @param  mixed $id applicantid
     * @return boolean
     */
    public function delete($id)
    {
        $affectedRows = 0;
        $applicant = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete('tblapplicants');
        if ($this->db->affected_rows() > 0) {
            logActivity('Applicant Deleted [Deleted by: ' . get_staff_full_name() . ', ID: ' . $id . ']');

            $affectedRows++;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

}
