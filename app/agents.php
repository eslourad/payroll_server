<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class agents extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'dob', 'email', 'title', 'employment_date', 'mobile_number', 'current_address', 'permanent_address', 'emergency_person', 'emergency_number', 'high_school', 'is_hs_grad', 'hs_grad_year', 'college', 'is_college_grad', 'college_grad_year', 'college_course', 'technical_school', 'is_ts_grad', 'ts_program', 'ts_year',  'image_file_name', 'image_file_size', 'image_date_uploaded', 'psa_file_name', 'psa_file_size', 'psa_date_uploaded', 'nbi_file_name', 'nbi_file_size', 'nbi_date_uploaded', 'sss', 'pag_ibig', 'phil_health', 'bank_name', 'account_name', 'account_number', 'branch_id', 'is_active', 'company_name', 'position', 'from', 'to', 'company_name_two', 'position_two', 'from_two', 'to_two', 'company_name_three', 'position_three', 'from_three', 'to_three', 'is_active', 'sss_file', 'pag_ibig_file', 'phil_health_file', 'allowance', 'isTL'
    ];
}