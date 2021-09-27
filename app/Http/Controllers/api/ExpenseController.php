<?php

namespace App\Http\Controllers\api;

use App\ApiRepositories\Interfaces\IExpenseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseRequest;
use App\MISC\ServiceResponse;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;

class ExpenseController extends Controller
{
    private $expenseRepository;
    private $userResponse;

    public function __construct(ServiceResponse $serviceResponse, IExpenseRepositoryInterface $expenseRepository)
    {
        $this->userResponse=$serviceResponse;
        $this->expenseRepository=$expenseRepository;
    }

    public function index()
    {
        try
        {
            return $this->userResponse->Success($this->expenseRepository->all());
        }
        catch (Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function paginate($page_no,$page_size)
    {
        try
        {
            return $this->userResponse->Success($this->expenseRepository->paginate($page_no,$page_size));
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function store(Request $request)
    {
        $user_id = Auth::id();
        $company_id=Str::getCompany($user_id);
        //check reference number already exist or not
        $already_exist = Expense::where('company_id',$company_id)->where('referenceNumber',$request->referenceNumber)->get();
        if(!$already_exist->isEmpty())
        {
            return $this->userResponse->Failed($sales = (object)[],'REFERENCE NUMBER ALREADY EXIST.');
        }
        $expense=$this->expenseRepository->insert($request);
        if($expense)
        {
            return $this->userResponse->Success($expense);
        }
        else
        {
            return $this->userResponse->Failed($sales = (object)[],'Something Went Wrong.');
        }
    }

    public function show($id)
    {
        try
        {
            $expense = Expense::find($id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->expenseRepository->getById($id);
            return $this->userResponse->Success($expense);
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }

    }

    public function update(Request $request)
    {
        try
        {
            $expense = Expense::find($request->id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->expenseRepository->update($request,$request->id);
            if($expense)
            {
                return $this->userResponse->Success($expense);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'Something Went Wrong.');
            }
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function ExpenseSearchByRef(Request $request)
    {
        try
        {
            $expense = $this->expenseRepository->ExpenseSearchByRef($request);
            if($expense)
            {
                return $this->userResponse->Success($expense);
            }
            else
            {
                return $this->userResponse->Failed($sales = (object)[],'Not Found.');
            }
        }
        catch(Exception $ex)
        {
            $this->userResponse->Exception($ex);
        }
    }

    public function destroy(Request $request,$Id)
    {
        try
        {
            $expense = Expense::find($Id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->expenseRepository->delete($request,$Id);
            return $this->userResponse->Success($expense);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function restore($Id)
    {
        try {
            $restore = Expense::withTrashed()->where('Id', $Id)->restore();
            return $this->userResponse->Success($restore);

        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public  function  trash()
    {
        $trashed = $this->expenseRepository->trashed();
        return $this->userResponse->Success($trashed);
    }

    public function BaseList()
    {
        $data = $this->expenseRepository->BaseList();
        return $this->userResponse->Success($data);
    }

    public function ExpenseDocumentsUpload(Request $request)
    {
        $this->expenseRepository->ExpenseDocumentsUpload($request);
        return $this->userResponse->Success($purchase = (object)['message'=>'Document(s) uploaded.']);
    }

    public function print($id)
    {
        try
        {
            $expense = Expense::find($id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $expense = $this->expenseRepository->print($id);
            return $this->userResponse->Success($expense);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }

    public function ActivateDeactivate($Id)
    {
        try
        {
            $expense = Expense::find($Id);
            if(is_null($expense))
            {
                return $this->userResponse->Failed($expense = (object)[],'Not Found.');
            }
            $result=$this->expenseRepository->ActivateDeactivate($Id);
            return $this->userResponse->Success($result);
        }
        catch (Exception $exception)
        {
            return $this->userResponse->Exception($exception);
        }
    }
}
