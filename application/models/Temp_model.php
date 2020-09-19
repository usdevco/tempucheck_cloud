<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Temp_model extends CRM_Model
{
    public function get($id=false) 
    {
        $this->db->where('id', $id);
        return $this->db->get('temp_data')->result();
    }

    public function get_all($id) 
    {
        $this->db->select('id, state, reading_qty, temp, pdf_file, added, userid');
        if($id)
        {
            $return = $this->db->where('userid',$id)->get('temp_data')->result();
            return $return;
        }
        else
        {
           $return = $this->db->get('temp_data')->result();
           return $return;
        }
    }

    public function update($id, $data) 
    {
        if($id) {
            $this->db->where('id', $id);
            return $this->db->update('temp_data', $data);
        } else {
            return $this->db->insert('temp_data', $data);
        }
    }
}
