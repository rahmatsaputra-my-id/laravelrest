<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Categories;
use App\postings;

class CategoriesController extends Controller
{
    public function getCategories(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];

        $wordlist = Categories::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){

            // $categoryName = Categories::select('category_name')->get();
            // $countTotal = postings::where('category', 'like', "%{$categoryName}%")->count();
            $categoriesVar = Categories::where('category_countHit', 'desc')->paginate(null);

            // $gabungan = [ 
            //     "data" => [
            //         $categoriesVar,
            //         // "count_total" => $countTotal
            //     ]
            // ];

            $custom = collect($dataSuccess);
            $pagination = $custom->merge($categoriesVar);
            // $pagination = $custom->merge($categoriesVar);

            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
    }

    public function postCategories(Request $request){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $nameTaken = [
        	'errors' => [['message' => 'Category Name Already Taken','code' => 40009]],
        	'meta' => ['http_status' => 409]
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 40004]],
        	'meta' => ['http_status' => 404],
    	];

        $requestCategory = $request->get('category_name');
        if(!$requestCategory == null){
            $dataCategoriesRequest = Categories::where('category_name','=', $requestCategory)->first();
    
            if ($dataCategoriesRequest == null){
                $categoriesVar = Categories::create([
                    'category_name' => $request->get('category_name'),
                    'category_countHit' => 0
                ]);
                return response()->json($dataSuccess, 200);
            }
            return response()->json($nameTaken,409);
        }
        return response()->json($errors,404);

    }
}
