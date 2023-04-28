<?php

namespace App\Repositories\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NearbyPropertyValidator
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     * @throws ValidationException
     */
    public function validate()
    {

        $validator = Validator::make(
            $this->request->all(),
            $this->rules(),
            $this->messages()
        );

        if ($validator->fails()) {

            throw new ValidationException(
                $validator,

                response()->json(
                    [
                        "code" => 422,
                        "message" => "OK",
                        "payload" => [
                            $validator->errors()->getMessages()
                        ],


                    ])

            );
        }

        return $validator->validateWithBag('post');
    }

    public function rules()
    {
        return [

            'radius' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            //'category' => ['required','integer']

        ];
    }

    public function messages()
    {

        return [
            'radius.required' => 'The :attribute is Required',
            'radius.integer' => 'The :attribute must be string',
            'latitude.required' => 'The :attribute is Required',
            'latitude.integer' => 'The :attribute must be string',
            'longitude.required' => 'The :attribute is Required',
            'longitude.integer' => 'The :attribute must be string',
            //'category.required' => 'The :attribute is Required.',
           // 'category.integer' => 'The :attribute is an Integer.'


        ];
    }

}


