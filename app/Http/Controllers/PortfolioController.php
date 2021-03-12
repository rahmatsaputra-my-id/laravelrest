<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Portfolios;

class PortfolioController extends Controller
{
    public function getPortfolio(){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $notFoundData = [
        	'errors' => [['message' => 'Not Found Any Data in Database','code' => 10004]],
        	'meta' => ['http_status' => 404]
        ];

        $wordlist = Portfolios::all('id');
        $wordCount = $wordlist->count();

        if($wordCount>0){
            
            $portFolioVar = Portfolios::orderBy('id', 'asc')->paginate(null);

            $custom = collect($dataSuccess);
            $pagination = $custom->merge($portFolioVar);

            return response()->json($pagination, 200);
        }
        return response()->json($notFoundData,404);
    }

    public function postPortfolio(Request $request){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 40004]],
        	'meta' => ['http_status' => 404],
        ];
        $someDataNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
    	];
        
        $requestType = $request->get('type');
        $requestTitle = $request->get('title');
        $requestSubtitle = $request->get('subtitle');
        $requestUrlToImage = $request->get('urlToImage');
        $requestNotes = $request->get('notes');

        if($request != null){
            if($requestType != null){
                if($requestTitle != null){
                    if($requestSubtitle != null){
                        if($requestUrlToImage != null){
                            if($requestNotes != null){

                                $portfolioVar = Portfolios::create([
                                    'type' => $request->get('type'),
                                    'title' => $request->get('title'),
                                    'subtitle' => $request->get('subtitle'),
                                    'urlToImage' => $request->get('urlToImage'),
                                    'notes' => $request->get('notes')
                                ]);
                                return response()->json($dataSuccess, 200);
                            }
                            return response()->json($someDataNull,422);
                        }
                        return response()->json($someDataNull,422);
                    }
                    return response()->json($someDataNull,422);
                }
                return response()->json($someDataNull,422);
            }
            return response()->json($someDataNull,422);
        }
        return response()->json($errors,404);
    }

    public function putPortfolio(Request $request, $id){
        $dataSuccess = [
            'status' => [['message' => 'OK','code' => 20000]],
            'meta' => ['http_status' => 200],
        ];
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 40004]],
        	'meta' => ['http_status' => 404],
        ];
        $someDataNull = [
        	'errors' => [['message' => 'Unprocessable Entity','code' => 10002]],
        	'meta' => ['http_status' => 422]
        ];
        
        $idRequest = Portfolios::find($id);
        
        $requestType = $request->get('type');
        $requestTitle = $request->get('title');
        $requestSubtitle = $request->get('subtitle');
        $requestUrlToImage = $request->get('urlToImage');
        $requestNotes = $request->get('notes');

        if(!$id == null){
            if($idRequest != null){
                if($request != null){
                    if($requestType != null){
                        if($requestTitle != null){
                            if($requestSubtitle != null){
                                if($requestUrlToImage != null){
                                    if($requestNotes != null){
        
                                        $portfolioVar = Portfolios::findOrFail($id);
                                        $portfolioVar -> update([
                                            'type' => $request->get('type'),
                                            'title' => $request->get('title'),
                                            'subtitle' => $request->get('subtitle'),
                                            'urlToImage' => $request->get('urlToImage'),
                                            'notes' => $request->get('notes')
                                        ]);
                                        return response()->json($dataSuccess, 200);
                                    }
                                    return response()->json($someDataNull,422);
                                }
                                return response()->json($someDataNull,422);
                            }
                            return response()->json($someDataNull,422);
                        }
                        return response()->json($someDataNull,422);
                    }
                    return response()->json($someDataNull,422);
                }
                return response()->json($errors,404);
            }
            return response()->json($errors,404);
        }
        return response()->json($errors,404);
    }

    public function deletePortfolio($id){
        $errors = [
        	'errors' => [['message' => 'Not Found','code' => 10004]],
        	'meta' => ['http_status' => 404],

    	];
		$dataSuccess = [
			'status' => [['message' => 'OK','code' => 10000]],
			'meta' => ['http_status' => 200],
		];
        $idRequest = Portfolios::find($id);
        
        if($id != null){
			if($idRequest != null){

				$portfolioData = Portfolios::find($id)->delete();
				return response()->json($dataSuccess,200);
			}
			return response()->json($errors,404);
		}
        return response()->json($errors,404);
    }
}
