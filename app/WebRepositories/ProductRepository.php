<?php


namespace App\WebRepositories;


use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Unit;
use App\WebRepositories\Interfaces\IProductRepositoryInterface;
use Illuminate\Http\Request;

class ProductRepository implements IProductRepositoryInterface
{
    public function index()
    {
        $products = Product::with('user','company')->get();
        return view('admin.product.index',compact('products'));
    }

    public function create()
    {
        return view('admin.product.create');
    }

    public function store(ProductRequest $productRequest)
    {
        $user_id = session('user_id');
        $company_id = session('company_id');
        $data =[
            'Name' =>$productRequest->Name,
            'Description' =>$productRequest->Description,
            'user_id' => $user_id ?? 0,
            'company_id' => $company_id ?? 0,
        ];
        Product::create($data);
        return redirect()->route('products.index')->with('success','Record Inserted Successfully');
    }

    public function update(Request $request, $Id)
    {
        $data = Product::find($Id);
        $user_id = session('user_id');
        $data->update([
            'Name' => $request->Name,
            'Description' => $request->Description,
            'user_id' => $user_id,
        ]);
        return redirect()->route('products.index')->with('update','Record Updated Successfully');
    }

    public function getById($Id)
    {
        // TODO: Implement getById() method.
    }

    public function edit($Id)
    {
        $product = Product::find($Id);
        return view('admin.product.edit',compact('product'));
    }

    public function delete(Request $request, $Id)
    {
        $data = Product::find($Id);
        $data->delete();
        return redirect()->route('products.index')->with('delete','Record Deleted Successfully');
    }

    public function restore($Id)
    {
        // TODO: Implement restore() method.
    }

    public function trashed()
    {
        // TODO: Implement trashed() method.
    }

    public function productDetails($Id)
    {
        $data = Product::with('units')->find($Id);
        return response()->json($data);
    }
}
