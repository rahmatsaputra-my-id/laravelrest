<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\postings;
use App\Categories;

class PostingController extends Controller
{
    public function postPostings(Request $request){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 40004]],
        	'meta' => ['http_status' => 404],
        ];
        $titleNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        $authorNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        $descriptionNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        
        $requestTitle = $request->get('title');
        $requestAuthor = $request->get('author');
        $requestDescription = $request->get('description');

        $requestCategory = $request->get('category');
        // $dataCategoryRequest = Categories::where('category_name', '=', $requestCategory )->first();

        if(!$requestTitle == null){
            if($requestAuthor != null){
                if($requestDescription != null){
                    $postingVar = postings::create([
                        'title' => $request->get('title'),
                        'author' => $request->get('author'),
                        'description' => $request->get('description'),
                        'urlToImage' => $request->get('urlToImage'),
                        'url' => $request->get('url'),
                        'tag' => $request->get('tag'),
                        'category' => $request->get('category'),
                        'countHit' => 0,
                    ]);

                    // if($requestCategory != null){
                    //     $countIncreaseCategory = $dataCategoryRequest->category_total;
                    //     if($dataCategoryRequest != null){
                    //         $categoryVar = Categories::update([
                    //             'category_total' => $countIncreaseCategory+1
                    //         ]);
                    //     }
                    // }

                    return response()->json($dataSuccess,201);
                }
                return response()->json($descriptionNull,422);
            }
            return response()->json($authorNull,422);
        }
        return response()->json($titleNull,422);
    }

    public function putPostings(Request $request, $id){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 40004]],
        	'meta' => ['http_status' => 404],
        ];
        $titleNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        $authorNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        $descriptionNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
        ];
        
        $idRequest = postings::find($id);

        $requestTitle = $request->get('title');
        $requestAuthor = $request->get('author');
        $requestDescription = $request->get('description');
        $requestCategory = $request->get('category');

        if(!$id == null){
            if($idRequest != null){

                if(!$requestTitle == null){
                    if($requestAuthor != null){
                        if($requestDescription != null){

                            $postingVar = postings::findOrFail($id);
                            $postingVar ->update([
                                'title' => $request->get('title'),
                                'author' => $request->get('author'),
                                'description' => $request->get('description'),
                                'urlToImage' => $request->get('urlToImage'),
                                'url' => $request->get('url'),
                                'tag' => $request->get('tag'),
                                'category' => $request->get('category'),
                                'countHit' => 0,
                            ]);
        
                            // if($requestCategory != null){
                            //     $countIncreaseCategory = $dataCategoryRequest->category_total;
                            //     if($dataCategoryRequest != null){
                            //         $categoryVar = Categories::update([
                            //             'category_total' => $countIncreaseCategory+1
                            //         ]);
                            //     }
                            // }
        
                            return response()->json($dataSuccess,201);
                        }
                        return response()->json($descriptionNull,422);
                    }
                    return response()->json($authorNull,422);
                }
                return response()->json($titleNull,422);
            }
            return response()->json($errors,404);
        }
        return response()->json($errors,404);
    }

    public function getPostings(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
    	];

        $wordlist = postings::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){
            // $postingVar = postings::orderBy('countHit', 'desc')->paginate(4);
            $postingVar = postings::orderBy('countHit', 'desc')->paginate(null);
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($postingVar);
    
            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
        
    }

    public function getPostingById($id){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];
        $idRequest =  postings::find($id);
        
        if($id != null){
            if($idRequest != null){
                $postingin = postings::where('id',$id)->paginate(40);
				$custom = collect($dataSuccess);
				$pagination = $custom->merge($postingin);
				return response()->json($pagination, 200);
            }
        }
        return response()->json($errors,404);
    }

    public function deletePosting($id){
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 10004]],
        	'meta' => ['http_status' => 404],

    	];
		$dataSuccess = [
			'status' => [['message' => 'OK','code' => 10000]],
			'meta' => ['http_status' => 200],
		];
        $idRequest = postings::find($id);
        
        if($id != null){
			if($idRequest != null){

				$booksData = postings::find($id)->delete();
				return response()->json($dataSuccess,200);
			}
			return response()->json($errors,404);
		}
        return response()->json($errors,404);
    }

    public function getRecents(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
    	];

        $wordlist = postings::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){
            $postingVar = postings::orderBy('id', 'desc')->paginate(3);
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($postingVar);
    
            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
    }

    public function getTags(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];
        
        $wordlist = postings::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){
            // $query1 = postings::select('tag')->distinct();
            // $perhitungan = postings::select('tag')->count();

            // $finalQuery = $query1->join($perhitungan)->paginate(null);
            // // $combo = [
            // //     "data" => [
            // //         $finalQuery,
            // //     ]
            // // ];

            // $postingVar = $finalQuery;

            $postingVar = postings::select('tag')->distinct()->paginate(null);
            
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($postingVar);
    
            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
    }

    public function getCategories(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];
        
        $wordlist = postings::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){
            // $query1 = postings::select('tag')->distinct();
            // $perhitungan = postings::select('tag')->count();

            // $finalQuery = $query1->join($perhitungan)->paginate(null);
            // // $combo = [
            // //     "data" => [
            // //         $finalQuery,
            // //     ]
            // // ];

            // $postingVar = $finalQuery;

            $postingVar = postings::select('category')->distinct()->paginate(null);
            
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($postingVar);
    
            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
    }
    
}
