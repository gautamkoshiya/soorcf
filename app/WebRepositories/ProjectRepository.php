<?php


namespace App\WebRepositories;


use App\Models\FileUpload;
use App\Models\Project;
use App\WebRepositories\Interfaces\IProjectRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class ProjectRepository implements IProjectRepositoryInterface
{
    public function index()
    {
        if(request()->ajax())
        {
            return datatables()->of(Project::latest()->get())
                ->addColumn('action', function ($data) {
                    $button = '<form action="'.route('projects.destroy', $data->id).'" method="POST">';
                    $button .= @csrf_field();
                    $button .= @method_field('DELETE');
                    $button .= '<a href="'.route('projects.edit', $data->id).'"  class=" btn btn-primary btn-sm"><i style="font-size: 20px" class="fa fa-edit"></i></a>';
                    $button .= '&nbsp;&nbsp;';
                    $button .= '<button type="button" class=" btn btn-danger btn-sm" onclick="ConfirmDelete()"><i style="font-size: 20px" class="fa fa-trash"></i></button>';
                    $button .= '</form>';
                    return $button;
                })
                ->addColumn('isActive', function($data){
                    if($data->isActive == true)
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'" checked><span class="slider"></span></label>';
                        return $button;
                    }
                    else
                    {
                        $button = '<label class="switch"><input onclick="change_status(this.value)" name="isActive" type="checkbox" value="customer_'.$data->id.'"><span class="slider"></span></label>';
                        return $button;
                    }
                })
                ->rawColumns([
                    'action',
                    'isActive',
                ])
                ->make(true);
        }
        return view('admin.project.index');
    }

    public function create()
    {
        return view('admin.project.create');
    }

    public function store(Request $request)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $logo=NULL;
        $signature=NULL;

        if ($request->hasFile('logo'))
        {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename=uniqid('project_logo_').'.'.$extension;
            $request->file('logo')->storeAs('project', $filename,'public');
            $logo=$filename;
        }

        if ($request->hasFile('signature'))
        {
            $file = $request->file('signature');
            $extension = $file->getClientOriginalExtension();
            $filename=uniqid('project_signature_').'.'.$extension;
            $request->file('signature')->storeAs('project', $filename,'public');
            $signature=$filename;
        }

        $project = [
            'Name' =>$request->Name,
            'Address' =>$request->Address,
            'Contact' =>$request->Contact,
            'Email' =>$request->Email,
            'TRN' =>$request->TRN,
            'FAX' =>$request->FAX,
            'manager_name' =>$request->manager_name,
            'registration_date' =>$request->registration_date,
            'renewal_date' =>$request->renewal_date,
            'user_id' =>$user_id,
            'company_id' =>$company_id,
            'logo' =>$logo,
            'signature' =>$signature,
        ];
        Project::create($project);
        return redirect()->route('projects.index');
    }

    public function show($Id)
    {
        // TODO: Implement show() method.
    }

    public function edit($Id)
    {
        $project = Project::find($Id);
        $base=URL::to('/storage/app/public/project/');
        //echo "<pre>";print_r($project);die;
        return view('admin.project.edit',compact('project','base'));
    }

    public function update(Request $request, $Id)
    {
        $project = Project::findOrFail($Id);
        $user_id = session('user_id');
        $logo=$request->previous_logo;
        $signature=$request->previous_signature;
        $base=URL::to('/storage/app/public/project/');

        if ($request->hasFile('logo'))
        {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename=uniqid('project_logo_').'.'.$extension;
            $request->file('logo')->storeAs('project', $filename,'public');
            $logo=$filename;

            //echo "<pre>";print_r(storage_path().'/app/public/project/'.$request->previous_logo);die;
            if($request->previous_logo!='')
            {
                if(file_exists(storage_path().'/app/public/project/'.$request->previous_logo)){
                    unlink(storage_path().'/app/public/project/'.$request->previous_logo);
                }
            }
        }

        if ($request->hasFile('signature'))
        {
            $file = $request->file('signature');
            $extension = $file->getClientOriginalExtension();
            $filename=uniqid('project_signature_').'.'.$extension;
            $request->file('signature')->storeAs('project', $filename,'public');
            $signature=$filename;

            if($request->previous_signature!='')
            {
                if(file_exists(storage_path().'/app/public/project/'.$request->previous_signature)){
                    unlink(storage_path().'/app/public/project/'.$request->previous_signature);
                }
            }
        }

        $project->update([
            'Name' =>$request->Name,
            'Address' =>$request->Address,
            'Contact' =>$request->Contact,
            'Email' =>$request->Email,
            'TRN' =>$request->TRN,
            'FAX' =>$request->FAX,
            'manager_name' =>$request->manager_name,
            'registration_date' =>$request->registration_date,
            'renewal_date' =>$request->renewal_date,
            'user_id' =>$user_id,
            'logo' =>$logo,
            'signature' =>$signature,
        ]);
        return redirect()->route('projects.index');
    }

    public function ChangeProjectStatus($id)
    {
        $project = Project::find($id);
        if($project->isActive==1)
        {
            $project->isActive=0;
        }
        else
        {
            $project->isActive=1;
        }
        $project->update();
        return Response()->json(true);
    }

    public function destroy($Id)
    {
        $data = Project::findOrFail($Id);
        $data->delete();
        return redirect()->route('projects.index');
    }
}
