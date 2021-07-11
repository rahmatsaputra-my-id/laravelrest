<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Books;
// use Auth;

class BookController extends Controller
{
	//	public function getFreeBook() {
	//        $dataSuccess = [
	//            'status' => [['message' => 'OK','code' => 20000]],
	//            'meta' => ['http_status' => 200],
	//        ];
	//        $book = Books::orderBy('id', 'desc')->paginate(40);
	//        $custom = collect($dataSuccess);
	//        $pagination = $custom->merge($book);
	//
	//		return response()->json($pagination, 200);
	//	}

	public function postBook(Request $request)
	{
		$dataSuccess = [
			'status' => [['message' => 'Created', 'code' => 20001]],
			'meta' => ['http_status' => 201],
		];
		$errors = [
			'errors' => [['message' => 'Unprocessable Entity', 'code' => 10002]],
			'meta' => ['http_status' => 422]
		];
		$invalidFileUpload = [
			'errors' => [['message' => 'Invalid File Upload', 'code' => 4000]],
			'meta' => ['http_status' => 400]
		];
		$invalidExtensionFile = [
			'errors' => [['message' => 'Forbidden Extension File', 'code' => 40003]],
			'meta' => ['http_status' => 403]
		];

		$nullFile = [
			'errors' => [['message' => 'File Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$forbidSize = [
			'errors' => [['message' => 'Forbidden Size File', 'code' => 40003]],
			'meta' => ['http_status' => 403]
		];

		if (
			$request->get('title') != null &&
			$request->get('author') != null &&
			$request->get('source') != null &&
			$request->get('description') != null
		) {
			$filenya = $request->file('urlToImage');

			if ($filenya == null) {
				return response()->json($nullFile, 404);
			}

			if (!$filenya->isValid()) {
				return response()->json($invalidFileUpload, 400);
			}

			$path = public_path() . '\fileUpload';

			if (
				$filenya->getClientOriginalExtension() != 'jpg' &&
				$filenya->getClientOriginalExtension() != 'png' &&
				$filenya->getClientOriginalExtension() != 'jfif' &&
				$filenya->getClientOriginalExtension() != 'jpeg'
			) {
				return response()->json($invalidExtensionFile, 403);
			}

			// 1 kilobit = 1000 bit
			// 1 kibibit = 1024 bit
			// 1 megabit = 976653 kibibit
			// 1 megabit = 1*(976653 * 1024)

			if ($filenya->getSize() > (2 * (976653 * 1024))) {
				return response()->json($forbidSize, 403);
			}

			$booksData = Books::create([
				'title' => $request->get('title'),
				'author' => $request->get('author'),
				'source' => $request->get('source'),
				'description' => $request->get('description'),
				'url' => $request->get('url'),
				'imageName' => $filenya->getClientOriginalName(),
				'urlToImage' => $path . $filenya->getClientOriginalName()
				// 'urlToImage' => $filenya->getRealPath()
			]);

			$filenya->move($path, $filenya->getClientOriginalName());

			return response()->json($dataSuccess, 201);
		}
		return response()->json($errors, 422);
	}

	public function getBooks(Request $request)
	{
		// $data = "Welcome " . Auth::user()->username;
		// $data = Books::orderByDesc('id')->get();
		// $wordlist = Books::all();
		// $wordCount = $wordlist->count();

		$dataSuccess = [
			'status' => [['message' => 'OK', 'code' => 20000]],
			'meta' => ['http_status' => 200],
		];
		$errors = [
			'errors' => [['message' => 'Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$errorsRequestNull = [
			'errors' => [['message' => 'Not Found, Request Null', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$notFoundData = [
			'errors' => [['message' => 'Not Found Any Data in Database', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$q = $request->get('q');
		$title = $request->get('title');
		$author = $request->get('author');
		$source = $request->get('source');

		$description = $request->get('description');
		$search_drivers_keywords = Books::where('title', 'like', "%{$q}%")
			->orWhere('author', 'like', "%{$q}%")
			->orWhere('source', 'like', "%{$q}%")
			->orWhere('description', 'like', "%{$q}%")
			->paginate(40);

		$search_drivers_title = Books::where('title', 'like', "%{$title}%")
			->paginate(40);

		$search_drivers_author = Books::where('author', 'like', "%{$author}%")
			->paginate(40);

		$search_drivers_source = Books::where('source', 'like', "%{$source}%")
			->paginate(40);

		$search_drivers_description = Books::where('description', 'like', "%{$description}%")
			->paginate(40);

		$searchCountKeywords = $search_drivers_keywords->count();
		$searchCountTitle = $search_drivers_title->count();
		$searchCountAuthor = $search_drivers_author->count();
		$searchCountSource = $search_drivers_source->count();
		$searchCountDescription = $search_drivers_description->count();

		$wordlist = Books::all('id');
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
						return response()->json($errors, 404);
					}
				}
				if ($title != null) {
					if ($search_drivers_title != null) {
						if ($searchCountTitle != 0) {

							$custom = collect($dataSuccess);
							$result = $custom->merge($search_drivers_title);

							return response()->json($result, 200);
						}
						return response()->json($errors, 404);
					}
				}
				if ($author != null) {
					if ($search_drivers_author != null) {
						if ($searchCountAuthor != 0) {

							$custom = collect($dataSuccess);
							$result = $custom->merge($search_drivers_author);

							return response()->json($result, 200);
						}
						return response()->json($errors, 404);
					}
				}
				if ($source != null) {
					if ($search_drivers_source != null) {
						if ($searchCountSource != 0) {

							$custom = collect($dataSuccess);
							$result = $custom->merge($search_drivers_source);

							return response()->json($result, 200);
						}
						return response()->json($errors, 404);
					}
				}
				if ($description != null) {
					if ($search_drivers_description != null) {
						if ($searchCountDescription != 0) {

							$custom = collect($dataSuccess);
							$result = $custom->merge($search_drivers_description);

							return response()->json($result, 200);
						}
						return response()->json($errors, 404);
					}
				}
				$book = Books::orderBy('id', 'desc')->paginate(40);
				$custom = collect($dataSuccess);
				$pagination = $custom->merge($book);

				return response()->json($pagination, 200);
			}
			return response()->json($notFoundData, 404);
		}
		return response()->json($errorsRequestNull, 404);
	}

	public function getBookById($id)
	{
		$errors = [
			'errors' => [['message' => 'Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$dataSuccess = [
			'status' => [['message' => 'OK', 'code' => 10000]],
			'meta' => ['http_status' => 200],
		];
		$idRequest =  Books::find($id);

		if ($id != null) {
			if ($idRequest != null) {

				$book = Books::where('id', $id)->paginate(40);
				$custom = collect($dataSuccess);
				$pagination = $custom->merge($book);
				return response()->json($pagination, 200);
			}
			return response()->json($errors, 404);
		}
		return response()->json($errors, 404);
	}

	public function putBookById(Request $request, $id)
	{
		$errors = [
			'errors' => [['message' => 'Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404],

		];
		$dataSuccess = [
			'status' => [['message' => 'OK', 'code' => 10000]],
			'meta' => ['http_status' => 200],
		];
		$errorsPut = [
			'errors' => [['message' => 'Unprocessable Entity', 'code' => 10002]],
			'meta' => ['http_status' => 422]
		];
		$invalidFileUpload = [
			'errors' => [['message' => 'Invalid File Upload', 'code' => 4000]],
			'meta' => ['http_status' => 400]
		];
		$invalidExtensionFile = [
			'errors' => [['message' => 'Forbidden Extension File', 'code' => 40003]],
			'meta' => ['http_status' => 403]
		];
		$nullFile = [
			'errors' => [['message' => 'File Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];
		$forbidSize = [
			'errors' => [['message' => 'Forbidden Size File', 'code' => 40003]],
			'meta' => ['http_status' => 403]
		];
		$idRequest =  Books::find($id);

		if ($id != null) {
			if ($idRequest != null) {

				if (
					$request->get('title') != null &&
					$request->get('author') != null &&
					$request->get('source') != null &&
					$request->get('description') != null
				) {
					$filenya = $request->file('urlToImage');

					if ($filenya == null) {
						return response()->json($nullFile, 404);
					}

					if (!$filenya->isValid()) {
						return response()->json($invalidFileUpload, 400);
					}

					// buat local
					//	$path = public_path().'\fileUpload\\';

					//buat staging
					$path = public_path() . '/';
					$pathSave = "https://staging.rahmatsaputra.my.id" . "/public/";

					if (
						$filenya->getClientOriginalExtension() != 'jpg' &&
						$filenya->getClientOriginalExtension() != 'png' &&
						$filenya->getClientOriginalExtension() != 'jfif' &&
						$filenya->getClientOriginalExtension() != 'jpeg'
					) {
						return response()->json($invalidExtensionFile, 403);
					}

					if ($filenya->getSize() > (2 * (976653 * 1024))) {
						return response()->json($forbidSize, 403);
					}

					$booksData = Books::findOrFail($id);
					$booksData->update([
						'title' => $request->get('title'),
						'author' => $request->get('author'),
						'source' => $request->get('source'),
						'description' => $request->get('description'),
						'url' => $request->get('url'),
						'imageName' => $filenya->getClientOriginalName(),
						'urlToImage' => $pathSave . $filenya->getClientOriginalName()
					]);
					$filenya->move($path, $filenya->getClientOriginalName());
					return response()->json($dataSuccess, 200);
				}
				return response()->json($errorsPut, 422);
			}
			return response()->json($errors, 404);
		}
		return response()->json($errors, 404);
	}

	public function deleteBookById($id)
	{
		$errors = [
			'errors' => [['message' => 'Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404],

		];
		$dataSuccess = [
			'status' => [['message' => 'OK', 'code' => 10000]],
			'meta' => ['http_status' => 200],
		];
		$idRequest = Books::find($id);

		if ($id != null) {
			if ($idRequest != null) {

				$booksData = Books::find($id)->delete();
				return response()->json($dataSuccess, 200);
			}
			return response()->json($errors, 404);
		}
		return response()->json($errors, 404);
	}

	public function deleteBooks(Request $request)
	{
		$errors = [
			'errors' => [['message' => 'Not Found', 'code' => 10004]],
			'meta' => ['http_status' => 404],
		];
		$errorNotSelect = [
			'errors' => [['message' => 'Not Found, Please Select Some Data', 'code' => 10004]],
			'meta' => ['http_status' => 404],
		];
		$idSelectedNotFound = [
			'errors' => [['message' => 'Not Found ID Selected in Database', 'code' => 10004]],
			'meta' => ['http_status' => 404],
		];
		$notFoundAnyData = [
			'errors' => [['message' => 'Not Found Any Data', 'code' => 10004]],
			'meta' => ['http_status' => 404]
		];

		$idSelected = $request->get('idSelected');
		$idRequest = Books::find(explode(',', $idSelected));

		$wordlist = Books::all('id');
		$wordCount = $wordlist->count();
		$successDeleteAll = [
			'status' => [['message' => $wordCount . ' Data Deleted', 'code' => 10000]],
			'meta' => ['http_status' => 200],
		];
		$deleteBooksList = Books::whereNotNull('id');


		if ($request != null) {
			if ($idSelected != null) {
				if ($wordCount > 0) {
					if ($idRequest->count() > 0) {
						$dataSuccess = [
							'status' => [['message' => $idRequest->count() . ' Data Deleted', 'code' => 10000]],
							'meta' => ['http_status' => 200],
						];
						$dbs = Books::whereIn('id', explode(',', $idSelected))->delete();
						//deleteall
						// $dbs = Books::delete('delete from books where id in('.implode(",", $ids).')');
						return response()->json($dataSuccess, 200);
					}
					return response()->json($idSelectedNotFound, 404);
				}
				return response()->json($notFoundAnyData, 404);
			}
			// return response()->json($errorNotSelect,404);

			if ($wordCount > 0) {
				$deleteBooksList->delete();
				return response()->json($successDeleteAll, 200);
			}
			return response()->json($notFoundAnyData, 404);
		}
		return response()->json($errors, 404);
	}
}
