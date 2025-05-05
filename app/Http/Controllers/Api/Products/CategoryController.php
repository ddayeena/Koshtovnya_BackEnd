<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return CategoryResource::collection($categories);
    }

    public function destroy(string $id){
        $category = Category::findOrFail($id);

        $category->delete();

        //Delete old image
        Cloudinary::destroy($category->image_public_id);

        return response()->json([
            'message' => 'Category deleted succeefully.'
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'name' => 'nullable|string|max:100'
        ]); 

        $category = Category::findOrFail($id);

        //Update data
        if (isset($data['name'])) {
            $category->name = $data['name'];
        }
        if (isset($data['image'])) {
            //Delete old image
            if ($category->image_public_id) {
                Cloudinary::destroy($category->image_public_id);
            }
    
            $imageData = $this->uploadImage($data['image']);
    
            $category->image_url = $imageData['url'];
            $category->image_public_id = $imageData['public_id'];
        }
    
        $category->save();
    

        return response()->json([
            'message' => 'Category updated succeefully.',
            'category' => CategoryResource::make($category)
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'name' => 'required|string|max:100'
        ]);

        $imageData = $this->uploadImage($data['image']);

        $category = Category::create([
            'name' => $data['name'],
            'image_url' => $imageData['url'],
            'image_public_id' => $imageData['public_id']
        ]);

        return response()->json([
            'message' => 'Category added successfully.',
            'category' => $category
        ]);
    }


    //Upload image on cloudinary
    public function uploadImage($image)
    {
        $im = $image->storeOnCloudinary('categories');
        return [
            'url' => $im->getSecurePath(),
            'public_id' => $im->getPublicId(),
        ];
    }
}
