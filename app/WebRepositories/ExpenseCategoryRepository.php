<?php


namespace App\WebRepositories;


use App\Http\Requests\ExpenseCategoryRequest;
use App\Models\ExpenseCategory;
use App\Models\UpdateNote;
use App\WebRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ExpenseCategoryRepository implements IExpenseCategoryRepositoryInterface
{
    public function index()
    {
        $expense_categories = ExpenseCategory::all();
        return view('admin.expense_category.index',compact('expense_categories'));
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function store(ExpenseCategoryRequest $expenseCategoryRequest)
    {
        $exist=ExpenseCategory::where('Name','like',$expenseCategoryRequest->Name)->first();
        if(!$exist)
        {
            $user_id = session('user_id');
            $company_id = session('company_id');
            $data = [
                'Name' => $expenseCategoryRequest->Name,
                'user_id' => $user_id,
                'company_id' => $company_id,
            ];
            ExpenseCategory::create($data);
            return redirect()->route('expense_categories.index')->with('success','Record Inserted Successfully');
        }
        else
        {
            return redirect()->route('expense_categories.index')->with('success','Already Exist');
        }

    }

    public function update(Request $request, $Id)
    {
        $data = ExpenseCategory::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data->update([
            'Name' => $request->Name,
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        return redirect()->route('expense_categories.index')->with('update','Record Update Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $category = ExpenseCategory::find($Id);
        return view('admin.expense_category.edit',compact('category'));
    }

    public function delete(Request $request, $Id)
    {
        $Update = ExpenseCategory::find($Id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $state = ExpenseCategory::withoutTrashed()->find($Id);
        if($state->trashed())
        {
            return redirect()->route('expense_categories.index');
        }
        else
        {
            $state->delete();
            return redirect()->route('expense_categories.index')->with('delete','Record Update Successfully');
        }
    }

    public function expense_category_delete_post(Request $request)
    {
        $response=false;
        $Update = ExpenseCategory::find($request->row_id);
        $user_id = session('user_id');
        $company_id = session('company_id');
        $Update->update([
            'user_id' => $user_id,
            'company_id' => $company_id,
        ]);
        $category = ExpenseCategory::withoutTrashed()->find($request->row_id);
        if($category->trashed())
        {
            //return redirect()->route('expense_categories.index');
            return Response()->json($response);
        }
        else
        {
            $category->delete();
            $response=true;

            $update_note = new UpdateNote();
            $update_note->RelationTable = 'expense_categories';
            $update_note->RelationId = $request->row_id;
            $update_note->UpdateDescription = $request->deleteDescription;
            $update_note->user_id = session('user_id');
            $update_note->company_id = $company_id;
            $update_note->save();

            return Response()->json($response);
            //return redirect()->route('expense_categories.index')->with('delete','Record Update Successfully');
        }
    }

    public function restore($Id)
    {
        $state = ExpenseCategory::onlyTrashed()->find($Id);
        if (!is_null($state))
        {
            $state->restore();
            return redirect()->route('expense_categories.index')->with('restore','Record Restore Successfully');
        }
        return redirect()->route('expense_categories.index');
    }

    public function trashed()
    {
        $trashes = ExpenseCategory::with('user')->onlyTrashed()->get();
        return view('admin.expense_category.edit',compact('trashes'));
    }
}
