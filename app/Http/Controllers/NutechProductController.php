<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\NutechProduct;
use Illuminate\Support\Facades\Validator;

class NutechProductController extends Controller
{
   public function postNutechProduct(Request $request)
   {
      $validator = Validator::make($request->all(), [
         'product_name' => 'required|string|max:255|unique:nutech_products',
         'purchase_price' => 'required|integer',
         'selling_price' => 'required|integer',
         'stock' => 'required|integer',
      ]);
      $errorsValidator = $validator->errors();
      $failedCreate = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Conflict', 'user' => $errorsValidator], 'code_detail' => 409],
         'meta' => ['http_status_code' => 400]
      ];
      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'Created', 'user' => 'Successfully created'], 'code_detail' => 201],
         'meta' => ['http_status_code' => 200]
      ];
      $errorsHeaders = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your headers'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsProductName = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Product Name'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsPurchasePrice = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Purchase Price'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsSellingPrice = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Selling Price'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsStock = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Stock'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $invalidFileUpload = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Invalid File Upload', 'user' => 'Check your file terms and condition before upload'], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];
      $invalidExtensionFile = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Forbidden Extension File', 'user' => 'Check your file extension (Only .jpg .png)'], 'code_detail' => 403],
         'meta' => ['http_status_code' => 400]
      ];
      $forbidSize = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Forbidden Size File', 'user' => 'Check your file size (Max 200kb)'], 'code_detail' => 403],
         'meta' => ['http_status_code' => 400]
      ];

      if ($request->hasHeader('Content-Type')) {
         if ($request->get('product_name') != null) {
            if ($request->get('purchase_price') != null) {
               if ($request->get('selling_price') != null) {
                  if ($request->get('stock') != null) {
                     if ($validator->fails()) {
                        return response()->json($failedCreate, 400);
                     }

                     $filenya = $request->file('url_to_image');

                     if ($filenya != null) {

                        if (!$filenya->isValid()) {
                           return response()->json($invalidFileUpload, 400);
                        }

                        // buat local
                        $pathUpload = public_path() . "/public/file";
                        $pathDownload = public_path() . "/public/file";

                        // buat staging
                        // $pathUpload = public_path() . '/public/file';
                        // $pathDownload = "https://staging.rahmatsaputra.my.id" . "/app/public/file/";

                        if (
                           $filenya->getClientOriginalExtension() != 'jpg' &&
                           $filenya->getClientOriginalExtension() != 'png'
                        ) {
                           return response()->json($invalidExtensionFile, 400);
                        }

                        $satuByte = 1024;
                        // $satuKB = 1 * $satuByte;
                        // $sepuluhKB = 10 * $satuByte;
                        $seratusKB = 100 * $satuByte;
                        // $satuMB = 1000 * $satuByte;

                        if ($filenya->getSize() > (2 * $seratusKB)) {
                           return response()->json($forbidSize, 400);
                        }

                        NutechProduct::create([
                           'product_name' => $request->get('product_name'),
                           'purchase_price' => $request->get('purchase_price'),
                           'selling_price' => $request->get('selling_price'),
                           'stock' => $request->get('stock'),
                           'image_name' => $filenya->getClientOriginalName(),
                           'url_to_image' => $pathDownload . $filenya->getClientOriginalName()
                        ]);
                        $filenya->move($pathUpload, $filenya->getClientOriginalName());
                        return response()->json($dataSuccess, 200);
                     }

                     NutechProduct::create([
                        'product_name' => $request->get('product_name'),
                        'purchase_price' => $request->get('purchase_price'),
                        'selling_price' => $request->get('selling_price'),
                        'stock' => $request->get('stock')
                     ]);

                     return response()->json($dataSuccess, 200);
                  }
                  return response()->json($errorsStock, 400);
               }
               return response()->json($errorsSellingPrice, 400);
            }
            return response()->json($errorsPurchasePrice, 400);
         }
         return response()->json($errorsProductName, 400);
      }
      return response()->json($errorsHeaders, 400);
   }

   public function getNutechProduct(Request $request)
   {
      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'OK'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Data Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsRequestNull = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found, Request Null'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $notFoundData = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found Any Data in Database'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $q = $request->get('q');
      $product_name = $request->get('product_name');
      $purchase_price = $request->get('purchase_price');
      $selling_price = $request->get('selling_price');
      $stock = $request->get('stock');

      $search_drivers_keywords = NutechProduct::where('product_name', 'like', "%{$q}%")
         ->orWhere('purchase_price', 'like', "%{$q}%")
         ->orWhere('selling_price', 'like', "%{$q}%")
         ->orWhere('stock', 'like', "%{$q}%")
         ->paginate((int) $request->get('per_page'));

      $search_drivers_product_name = NutechProduct::where('product_name', 'like', "%{$product_name}%")
         ->paginate((int) $request->get('per_page'));

      $search_drivers_purchase_price = NutechProduct::where('purchase_price', 'like', "%{$purchase_price}%")
         ->paginate((int) $request->get('per_page'));

      $search_drivers_selling_price = NutechProduct::where('selling_price', 'like', "%{$selling_price}%")
         ->paginate((int) $request->get('per_page'));

      $search_drivers_stock = NutechProduct::where('stock', 'like', "%{$stock}%")
         ->paginate((int) $request->get('per_page'));

      $searchCountKeywords = $search_drivers_keywords->count();
      $searchCountProductName = $search_drivers_product_name->count();
      $searchCountPurchasePrice = $search_drivers_purchase_price->count();
      $searchCountSellingPrice = $search_drivers_selling_price->count();
      $searchCountStock = $search_drivers_stock->count();

      $wordlist = NutechProduct::all('id');
      $wordCount = $wordlist->count();

      if ($request != null) {
         if ($wordCount > 0) {
            if ($q != null) {
               if ($search_drivers_keywords != null) {
                  if ($searchCountKeywords != 0) {

                     $custom = collect($dataSuccess);
                     $result = $custom->merge($search_drivers_keywords);

                     return response()->json($result, 200);
                  }
                  return response()->json($errors, 400);
               }
            }
            if ($product_name != null) {
               if ($search_drivers_product_name != null) {
                  if ($searchCountProductName != 0) {

                     $custom = collect($dataSuccess);
                     $result = $custom->merge($search_drivers_product_name);

                     return response()->json($result, 200);
                  }
                  return response()->json($errors, 400);
               }
            }
            if ($purchase_price != null) {
               if ($search_drivers_purchase_price != null) {
                  if ($searchCountPurchasePrice != 0) {

                     $custom = collect($dataSuccess);
                     $result = $custom->merge($search_drivers_purchase_price);

                     return response()->json($result, 200);
                  }
                  return response()->json($errors, 400);
               }
            }
            if ($selling_price != null) {
               if ($search_drivers_selling_price != null) {
                  if ($searchCountSellingPrice != 0) {

                     $custom = collect($dataSuccess);
                     $result = $custom->merge($search_drivers_selling_price);

                     return response()->json($result, 200);
                  }
                  return response()->json($errors, 400);
               }
            }
            if ($stock != null) {
               if ($search_drivers_stock != null) {
                  if ($searchCountStock != 0) {

                     $custom = collect($dataSuccess);
                     $result = $custom->merge($search_drivers_stock);

                     return response()->json($result, 200);
                  }
                  return response()->json($errors, 400);
               }
            }
            $nutechProduct = NutechProduct::orderBy('id', 'desc')->paginate((int) $request->get('per_page'));
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($nutechProduct);

            return response()->json($pagination, 200);
         }
         return response()->json($notFoundData, 400);
      }
      return response()->json($errorsRequestNull, 400);
   }

   public function getNutechProductById(Request $request, $id)
   {
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Data Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];
      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'OK'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];
      $idRequest =  NutechProduct::find($id);

      if ($id != null) {
         if ($idRequest != null) {

            $nutechProduct = NutechProduct::where('id', $id)->paginate((int) $request->get('per_page'));
            $custom = collect($dataSuccess);
            $pagination = $custom->merge($nutechProduct);
            return response()->json($pagination, 200);
         }
         return response()->json($errors, 400);
      }
      return response()->json($errors, 400);
   }

   public function putNutechProductById(Request $request, $id)
   {
      $validator = Validator::make($request->all(), [
         'product_name' => 'required|string|max:255',
         'purchase_price' => 'required|integer',
         'selling_price' => 'required|integer',
         'stock' => 'required|integer',
      ]);

      $errorsValidator = $validator->errors();

      $failedCreate = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Conflict', 'user' => $errorsValidator], 'code_detail' => 409],
         'meta' => ['http_status_code' => 400]
      ];

      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Data Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'OK'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];

      $invalidExtensionFile = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Forbidden Extension File', 'user' => 'Check your file extension (Only .jpg .png)'], 'code_detail' => 403],
         'meta' => ['http_status_code' => 400]
      ];
      $forbidSize = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Forbidden Size File', 'user' => 'Check your file size (Max 200kb)'], 'code_detail' => 403],
         'meta' => ['http_status_code' => 400]
      ];
      $invalidFileUpload = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Invalid File Upload', 'user' => 'Check your file terms and condition before upload'], 'code_detail' => 400],
         'meta' => ['http_status_code' => 400]
      ];

      $errorsHeaders = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your headers'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsProductName = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Product Name'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsPurchasePrice = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Purchase Price'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsSellingPrice = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Selling Price'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];
      $errorsStock = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Unprocessable Entity', 'user' => 'Check your key Stock'], 'code_detail' => 422],
         'meta' => ['http_status_code' => 400]
      ];


      $idRequest =  NutechProduct::find($id);

      if ($id != null) {
         if ($idRequest != null) {

            if ($request->hasHeader('Content-Type')) {
               if ($request->get('product_name') != null) {
                  if ($request->get('purchase_price') != null) {
                     if ($request->get('selling_price') != null) {
                        if ($request->get('stock') != null) {
                           if ($validator->fails()) {
                              return response()->json($failedCreate, 400);
                           }

                           $filenya = $request->file('url_to_image');

                           if ($filenya != null) {
                              // return response()->json($nullFile, 400);


                              if (!$filenya->isValid()) {
                                 return response()->json($invalidFileUpload, 400);
                              }

                              // buat local
                              $pathUpload = public_path() . "/public/file";
                              $pathDownload = public_path() . "/public/file";

                              // buat staging
                              // $pathUpload = public_path() . '/public/file';
                              // $pathDownload = "https://staging.rahmatsaputra.my.id" . "/app/public/file/";

                              if (
                                 $filenya->getClientOriginalExtension() != 'jpg' &&
                                 $filenya->getClientOriginalExtension() != 'png'
                              ) {
                                 return response()->json($invalidExtensionFile, 400);
                              }

                              $satuByte = 1024;
                              // $satuKB = 1 * $satuByte;
                              // $sepuluhKB = 10 * $satuByte;
                              $seratusKB = 100 * $satuByte;
                              // $satuMB = 1000 * $satuByte;

                              if ($filenya->getSize() > (2 * $seratusKB)) {
                                 return response()->json($forbidSize, 400);
                              }

                              $nutechProductData = NutechProduct::findOrFail($id);
                              $nutechProductData->update([
                                 'product_name' => $request->get('product_name'),
                                 'purchase_price' => $request->get('purchase_price'),
                                 'selling_price' => $request->get('selling_price'),
                                 'stock' => $request->get('stock'),
                                 'image_name' => $filenya->getClientOriginalName(),
                                 'url_to_image' => $pathDownload . $filenya->getClientOriginalName()
                              ]);
                              $filenya->move($pathUpload, $filenya->getClientOriginalName());
                              return response()->json($dataSuccess, 200);
                           }

                           $nutechProductData = NutechProduct::findOrFail($id);
                           $nutechProductData->update([
                              'product_name' => $request->get('product_name'),
                              'purchase_price' => $request->get('purchase_price'),
                              'selling_price' => $request->get('selling_price'),
                              'stock' => $request->get('stock')
                           ]);

                           return response()->json($dataSuccess, 200);
                        }
                        return response()->json($errorsStock, 400);
                     }
                     return response()->json($errorsSellingPrice, 400);
                  }
                  return response()->json($errorsPurchasePrice, 400);
               }
               return response()->json($errorsProductName, 400);
            }
            return response()->json($errorsHeaders, 400);
         }
         return response()->json($errors, 400);
      }
      return response()->json($errors, 400);
   }

   public function deleteNutechProductById($id)
   {
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Data Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $dataSuccess = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'OK'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];

      $idRequest = NutechProduct::find($id);

      if ($id != null) {
         if ($idRequest != null) {

            NutechProduct::find($id)->delete();
            return response()->json($dataSuccess, 200);
         }
         return response()->json($errors, 400);
      }
      return response()->json($errors, 400);
   }

   public function deleteNutechProduct(Request $request)
   {
      $errors = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Data Not Found'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $idSelectedNotFound = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found ID Selected in Database'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $notFoundAnyData = [
         'status' => ['messages' => ['subject' => 'failed', 'system' => 'Not Found', 'user' => 'Not Found Any Data'], 'code_detail' => 404],
         'meta' => ['http_status_code' => 400]
      ];

      $idSelected = $request->get('idSelected');
      $idRequest = NutechProduct::find(explode(',', $idSelected));

      $wordlist = NutechProduct::all('id');
      $wordCount = $wordlist->count();

      $successDeleteAll = [
         'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => 'Data deleted'], 'code_detail' => 200],
         'meta' => ['http_status_code' => 200]
      ];

      $deleteNutechProductList = NutechProduct::whereNotNull('id');


      if ($request != null) {
         if ($idSelected != null) {
            if ($wordCount > 0) {
               if ($idRequest->count() > 0) {
                  $dataSuccess = [
                     'status' => ['messages' => ['subject' => 'success', 'system' => 'OK', 'user' => $idRequest->count() . ' Data Deleted'], 'code_detail' => 200],
                     'meta' => ['http_status_code' => 200]
                  ];
                  NutechProduct::whereIn('id', explode(',', $idSelected))->delete();
                  return response()->json($dataSuccess, 200);
               }
               return response()->json($idSelectedNotFound, 400);
            }
            return response()->json($notFoundAnyData, 400);
         }

         if ($wordCount > 0) {
            $deleteNutechProductList->delete();
            return response()->json($successDeleteAll, 200);
         }
         return response()->json($notFoundAnyData, 400);
      }
      return response()->json($errors, 400);
   }
}
