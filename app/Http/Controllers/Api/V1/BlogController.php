<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBlogRequest;
use App\Http\Requests\Api\V1\UpdateBlogRequest;
use App\Http\Resources\V1\BlogResource;
use App\Models\Blog;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'blogs' => BlogResource::collection(Blog::latest()->get())
        ];
        return response()->successResponse($data, 'Blog Retrieved SuccessFul.', Response::HTTP_FOUND);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreBlogRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogRequest $request)
    {
        try {
            $validated = $request->validated();
            $blog = Blog::create($validated);
            $data = [
                'blog' => new BlogResource($blog)
            ];
            return response()->successResponse($data, 'Blog Created SuccessFul.', Response::HTTP_CREATED);
        } catch (\Exception $exception) {
            saveApiErrorLog('error', $exception);
            Log::info($exception->getMessage());
            return response()->errorResponse();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
//        try{
            $blog = Blog::with(['createdBy:id,name','category:id,name'])->where(['id' => $id])->first();
            if($blog){
                return response()->successResponse(new BlogResource($blog), 'Blog details', 200);
            }else{
                return response()->notFoundResponse();
            }

//        }catch(Exception $exception){
//            Log::info($exception->getMessage());
//            return response()->errorResponse();
//        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function edit(Blog $blog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\Api\V1\UpdateBlogRequest $request
     * @param \App\Models\Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Blog $blog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Blog $blog)
    {
        //
    }
}
