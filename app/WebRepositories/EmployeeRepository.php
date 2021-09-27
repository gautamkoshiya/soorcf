<?php


namespace App\WebRepositories;


use App\Http\Requests\EmployeeRquest;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Gender;
use App\Models\Nationality;
use App\Models\Project;
use App\Models\Region;
use App\WebRepositories\Interfaces\IEmployeeRepositoryInterface;
use Illuminate\Http\Request;
use App\Models\AccountTransaction;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class EmployeeRepository implements IEmployeeRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Employee::where('company_id',session('company_id'))->latest()->get())
            ->addColumn('action', function ($data) {
                $button = '<a href="' . url('deleteEmployee', $data->id) . '" onclick="return ConfirmDelete()"  class="btn btn-danger btn-sm"><i style="font-size: 20px" class="fa fa-trash"></i></i></a>';
                $button .= '&nbsp;&nbsp;';
                $button .= '<a href="'.route('employees.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                $button .= '</form>';
                $button .='&nbsp;<button class="btn btn-dark" onclick="show_detail(this.id)" type="button" id="show_'.$data->id.'"><i style="font-size: 20px" class="fa fa-eye"></i></button>';
                return $button;
            })
            ->addColumn('isActive', function($data)
            {
                if($data->isActive == true)
                {
                    $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="emp_'.$data->id.'" checked><span class="slider"></span></label>';
                    return $button;
                }
                else
                {
                    $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="emp_'.$data->id.'"><span class="slider"></span></label>';
                    return $button;
                }
            })
            ->rawColumns([
                'action',
                'isActive',
            ])
            ->make(true);
        }
        return view('admin.employee.index');
    }

    public function getEmployeeDetail($Id)
    {
        $base=URL::to('/storage/app/public/employee_docs/');
        $employee=Employee::with(['company'=> function($q){$q->select('id','Name');},'designation'=> function($q){$q->select('id','Name');},'department'=> function($q){$q->select('id','Name');},'gender'=> function($q){$q->select('id','Name');},'user'=> function($q){$q->select('id','name');},'nationality'=> function($q){$q->select('id','Name');}])->where('id',$Id)->first();
        $html='<table class="table table-sm"><tbody>';
        $html.='<tr class="bg-success"><td>Name </td><td>'.$employee->Name.'</td></tr>';
        $html.='<tr><td>Mobile No. </td><td colspan="3">'.$employee->Mobile.'</td></tr>';
        $html.='<tr><td>Email </td><td colspan="3">'.$employee->email.'</td></tr>';
        $html.='<tr><td>Birth Date </td><td colspan="3">'.date('d-M-Y',strtotime($employee->DOB)).'</td></tr>';
        $html.='<tr><td>Nationality </td><td colspan="3">'.($employee->nationality->Name ?? "N.A.").'</td></tr>';
        $html.='<tr><td>Gender </td><td colspan="3">'.($employee->gender->Name ?? "N.A.").'</td></tr>';
        $html.='<tr><td>Department </td><td colspan="3">'.($employee->department->Name ?? "N.A.").'</td></tr>';
        $html.='<tr><td>Designation </td><td colspan="3">'.($employee->designation->Name ?? "N.A.").'</td></tr>';
        $html.='<tr><td>Labour Code </td><td colspan="3">'.($employee->labour_code ?? "N.A.").'</td></tr>';
        $html.='<tr><td>Passport Exp. Date : </td>
            <td colspan="3">'.date('d-M-Y',strtotime($employee->passport_expire_date)).'</td>
            <td>Passport File : </td>
            <td colspan="3">';
                if($employee->passport_doc!='')
                {
                    $html.='<a target="_blank" href="'.$base.'/'.$employee->passport_doc.'"><i class="ti ti-eye"></i> View File</a>';
                }
                else
                {
                    $html.='N.A.';
                }
            $html.='</td></tr>';
            $html.='<tr><td>Visa Exp. Date : </td>
            <td colspan="3">'.date('d-M-Y',strtotime($employee->visa_expire_date)).'</td>
            <td>Visa File : </td>
            <td colspan="3">';
                if($employee->visa_doc!='')
                {
                    $html.='<a target="_blank" href="'.$base.'/'.$employee->visa_doc.'"><i class="ti ti-eye"></i> View File</a>';
                }
                else
                {
                    $html.='N.A.';
                }
                $html.='</td></tr>';

            $html.='<tr><td>Insurance Exp. Date : </td>
            <td colspan="3">'.date('d-M-Y',strtotime($employee->insurance_expire_date)).'</td>
            <td>Insurance File : </td>
            <td colspan="3">';
                if($employee->insurance_doc!='')
                {
                    $html.='<a target="_blank" href="'.$base.'/'.$employee->insurance_doc.'"><i class="ti ti-eye"></i> View File</a>';
                }
                else
                {
                    $html.='N.A.';
                }
                $html.='</td></tr>';

            $html.='<tr><td>Driving License Exp. Date : </td>
            <td colspan="3">'.date('d-M-Y',strtotime($employee->driving_licence_expire_date)).'</td>
            <td>Driving License File : </td>
            <td colspan="3">';
            if($employee->driving_licence_doc!='')
            {
                $html.='<a target="_blank" href="'.$base.'/'.$employee->driving_licence_doc.'"><i class="ti ti-eye"></i> View File</a>';
            }
            else
            {
                $html.='N.A.';
            }
            $html.='</td></tr>';

            $html.='<tr><td>Emirates ID Exp. Date : </td>
            <td colspan="3">'.date('d-M-Y',strtotime($employee->emi_id_expire_date)).'</td>
            <td>Emirates ID File : </td>
            <td colspan="3">';
            if($employee->emi_id_doc!='')
            {
                $html.='<a target="_blank" href="'.$base.'/'.$employee->emi_id_doc.'"><i class="ti ti-eye"></i> View File</a>';
            }
            else
            {
                $html.='N.A.';
            }
            $html.='</td></tr>';

        $html.='<div class="row text-right"><div class="col-md-12"><label>(created by : '.$employee->user->name.'-'.$employee->created_at.')</label></div></div>';
        $html.='</tbody></table>';
        return Response()->json($html);
    }

    public function create()
    {
        $regions = Region::all();
        $companies = Company::select('id','Name')->where('isActive',1)->get();
        $department = Department::select('id','Name')->get();
        $designation = Designation::select('id','Name')->get();
        $nationality = Nationality::select('id','Name')->get();
        $gender = Gender::select('id','Name')->get();
        $projects = Project::select('id','Name')->get();
        return view('admin.employee.create',compact('regions','companies','department','designation','nationality','gender','projects'));
    }

    public function store(EmployeeRquest $employeeRequest)
    {
        $user_id = session('user_id');
        $photo='';
        if ($employeeRequest->hasfile('photo'))
        {
            $document=$employeeRequest->file('photo');
            $extension = $document->getClientOriginalExtension();
            $photo=uniqid('photo_').'.'.$extension;
            $document->storeAs('employee_docs/',$photo,'public');
        }

        $passport_doc='';
        if ($employeeRequest->hasfile('passport_doc'))
        {
            $document=$employeeRequest->file('passport_doc');
            $extension = $document->getClientOriginalExtension();
            $passport_doc=uniqid('passport_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$passport_doc,'public');
        }

        $visa_doc='';
        if ($employeeRequest->hasfile('visa_doc'))
        {
            $document=$employeeRequest->file('visa_doc');
            $extension = $document->getClientOriginalExtension();
            $visa_doc=uniqid('visa_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$visa_doc,'public');
        }

        $insurance_doc='';
        if ($employeeRequest->hasfile('insurance_doc'))
        {
            $document=$employeeRequest->file('insurance_doc');
            $extension = $document->getClientOriginalExtension();
            $insurance_doc=uniqid('insurance_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$insurance_doc,'public');
        }

        $driving_licence_doc='';
        if ($employeeRequest->hasfile('driving_licence_doc'))
        {
            $document=$employeeRequest->file('driving_licence_doc');
            $extension = $document->getClientOriginalExtension();
            $driving_licence_doc=uniqid('driving_licence_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$driving_licence_doc,'public');
        }

        $emi_id_doc='';
        if ($employeeRequest->hasfile('emi_id_doc'))
        {
            $document=$employeeRequest->file('emi_id_doc');
            $extension = $document->getClientOriginalExtension();
            $emi_id_doc=uniqid('emi_id_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$emi_id_doc,'public');
        }

        $other_doc='';
        if ($employeeRequest->hasfile('other_doc'))
        {
            $document=$employeeRequest->file('other_doc');
            $extension = $document->getClientOriginalExtension();
            $other_doc=uniqid('other_doc_').'.'.$extension;
            $document->storeAs('employee_docs/',$other_doc,'public');
        }

        $data = [
            'Name' => $employeeRequest->Name,
            'emergencyContactNumber' => $employeeRequest->emergencyContactNumber,
            'Mobile' => $employeeRequest->Mobile,
            'company_id' => $employeeRequest->company_id,
            'department_id' => $employeeRequest->department_id,
            'designation_id' => $employeeRequest->designation_id,
            'nationality_id' => $employeeRequest->nationality_id,
            'gender_id' => $employeeRequest->gender_id,
            'startOfJob' => $employeeRequest->startOfJob,
            'DOB' => $employeeRequest->DOB ?? date('Y-m-d'),
            'photo' => $photo,
            'Basic' => $employeeRequest->Basic,
            'labour_code' => $employeeRequest->labour_code,
            'email' => $employeeRequest->email,
            'passportNumber' => $employeeRequest->passportNumber,
            'passport_issue_date' => $employeeRequest->passport_issue_date,
            'passport_expire_date' => $employeeRequest->passport_expire_date,
            'passport_doc' => $passport_doc,
            'visa_reference_number' => $employeeRequest->visa_reference_number,
            'visa_issue_date' => $employeeRequest->visa_issue_date,
            'visa_expire_date' => $employeeRequest->visa_expire_date,
            'visa_doc' => $visa_doc,
            'insurance_reference_number' => $employeeRequest->insurance_reference_number,
            'insurance_issue_date' => $employeeRequest->insurance_issue_date,
            'insurance_expire_date' => $employeeRequest->insurance_expire_date,
            'insurance_doc' => $insurance_doc,
            'driving_licence_reference_number' => $employeeRequest->driving_licence_reference_number,
            'driving_licence_issue_date' => $employeeRequest->driving_licence_issue_date,
            'driving_licence_expire_date' => $employeeRequest->driving_licence_expire_date,
            'driving_licence_doc' => $driving_licence_doc,
            'identityNumber' => $employeeRequest->identityNumber,
            'emi_id_issue_date' => $employeeRequest->emi_id_issue_date,
            'emi_id_expire_date' => $employeeRequest->emi_id_expire_date,
            'emi_id_doc' => $emi_id_doc,
            'other_reference_number' => $employeeRequest->other_reference_number,
            'other_issue_date' => $employeeRequest->other_issue_date,
            'other_expire_date' => $employeeRequest->other_expire_date,
            'other_doc' => $other_doc,
            'Address' => $employeeRequest->Address,
            'region_id' => $employeeRequest->region_id ?? 0,
            'Description' => $employeeRequest->Description,
            'UpdateDescription' => $employeeRequest->UpdateDescription,//project id
            'user_id' => $user_id,
            'isActive' => 1,
        ];
        $employee = Employee::create($data);
        if ($employee)
        {
            $account = new AccountTransaction([
                'employee_id' => $employee->id,
                'user_id' => $user_id,
                'company_id' => $company_id ?? 0,
                'Description' => 'initial',
            ]);
        }
        $employee->account_transaction()->save($account);
        return redirect()->route('employees.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $user_id = session('user_id');
        $data = Employee::find($Id);
        if($data)
        {
            $values=[
                'Name' => $request->Name,
                'emergencyContactNumber' => $request->emergencyContactNumber,
                'Mobile' => $request->Mobile,
                'company_id' => $request->company_id,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'nationality_id' => $request->nationality_id,
                'gender_id' => $request->gender_id,
                'startOfJob' => $request->startOfJob,
                'DOB' => $request->DOB ?? date('Y-m-d'),
                'Basic' => $request->Basic,
                'labour_code' => $request->labour_code,
                'email' => $request->email,
                'passportNumber' => $request->passportNumber,
                'passport_issue_date' => $request->passport_issue_date,
                'passport_expire_date' => $request->passport_expire_date,
                'visa_reference_number' => $request->visa_reference_number,
                'visa_issue_date' => $request->visa_issue_date,
                'visa_expire_date' => $request->visa_expire_date,
                'insurance_reference_number' => $request->insurance_reference_number,
                'insurance_issue_date' => $request->insurance_issue_date,
                'insurance_expire_date' => $request->insurance_expire_date,
                'driving_licence_reference_number' => $request->driving_licence_reference_number,
                'driving_licence_issue_date' => $request->driving_licence_issue_date,
                'driving_licence_expire_date' => $request->driving_licence_expire_date,
                'identityNumber' => $request->identityNumber,
                'emi_id_issue_date' => $request->emi_id_issue_date,
                'emi_id_expire_date' => $request->emi_id_expire_date,
                'other_reference_number' => $request->other_reference_number,
                'other_issue_date' => $request->other_issue_date,
                'other_expire_date' => $request->other_expire_date,
                'Address' => $request->Address,
                'region_id' => $request->region_id ?? 0,
                'Description' => $request->Description,
                'UpdateDescription' => $request->UpdateDescription,//project id
                'user_id' => $user_id,
            ];

            if ($request->hasfile('photo'))
            {
                $document=$request->file('photo');
                $extension = $document->getClientOriginalExtension();
                $photo=uniqid('photo_').'.'.$extension;
                $document->storeAs('employee_docs/',$photo,'public');
                $values['photo']=$photo;
            }

            if ($request->hasfile('passport_doc'))
            {
                $document=$request->file('passport_doc');
                $extension = $document->getClientOriginalExtension();
                $passport_doc=uniqid('passport_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$passport_doc,'public');
                $values['passport_doc']=$passport_doc;
            }

            if ($request->hasfile('visa_doc'))
            {
                $document=$request->file('visa_doc');
                $extension = $document->getClientOriginalExtension();
                $visa_doc=uniqid('visa_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$visa_doc,'public');
                $values['visa_doc']=$visa_doc;
            }

            if ($request->hasfile('insurance_doc'))
            {
                $document=$request->file('insurance_doc');
                $extension = $document->getClientOriginalExtension();
                $insurance_doc=uniqid('insurance_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$insurance_doc,'public');
                $values['insurance_doc']=$insurance_doc;
            }

            if ($request->hasfile('driving_licence_doc'))
            {
                $document=$request->file('driving_licence_doc');
                $extension = $document->getClientOriginalExtension();
                $driving_licence_doc=uniqid('driving_licence_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$driving_licence_doc,'public');
                $values['driving_licence_doc']=$driving_licence_doc;
            }

            if ($request->hasfile('emi_id_doc'))
            {
                $document=$request->file('emi_id_doc');
                $extension = $document->getClientOriginalExtension();
                $emi_id_doc=uniqid('emi_id_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$emi_id_doc,'public');
                $values['emi_id_doc']=$emi_id_doc;
            }

            if ($request->hasfile('other_doc'))
            {
                $document=$request->file('other_doc');
                $extension = $document->getClientOriginalExtension();
                $other_doc=uniqid('other_doc_').'.'.$extension;
                $document->storeAs('employee_docs/',$other_doc,'public');
                $values['other_doc']=$other_doc;
            }
            $data->update($values);

            //if there is no account transaction make initial entry
            $check=AccountTransaction::where('employee_id',$data->id)->get();
            if(!$check->first())
            {
                $account = new AccountTransaction([
                    'employee_id' => $data->id,
                    'user_id' => $user_id,
                    'company_id' => $data->company_id,
                    'Description' => 'initial',
                ]);
                $account->save();
            }
            return redirect()->route('employees.index')->with('update','Record Updated Successfully');
        }
        else
        {
            return redirect()->route('employees.index')->with('message','Something went wrong...');
        }
    }

    public function ChangeEmployeeStatus($Id)
    {
        $employee = Employee::find($Id);
        if($employee->isActive==1)
        {
            $employee->isActive=0;
        }
        else
        {
            $employee->isActive=1;
        }
        $employee->update();
        return Response()->json(true);
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $regions = Region::all();
        $employee = Employee::with('region')->find($Id);
        $companies = Company::select('id','Name')->where('isActive',1)->get();
        $department = Department::select('id','Name')->get();
        $designation = Designation::select('id','Name')->get();
        $nationality = Nationality::select('id','Name')->get();
        $gender = Gender::select('id','Name')->get();
        $projects = Project::select('id','Name')->get();
        return view('admin.employee.edit',compact('regions','employee','companies','department','designation','nationality','gender','projects'));
    }

    public function delete($Id)
    {
        $update = Employee::find($Id);
        $user_id = session('user_id');
        $update->update([
            'user_id' => $user_id,
        ]);
        $state = Employee::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('employees.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('employees.index')->with('delete','Record Deleted Successfully');
        }

    }

    public function restore($Id)
    {
        $state = Employee::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return redirect()->route('employees.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('employees.index');
    }

    public function trashed()
    {
        $trashes = Employee::with('user')->onlyTrashed()->get();
        return view('admin.employees.edit',compact('trashes'));
    }
}
