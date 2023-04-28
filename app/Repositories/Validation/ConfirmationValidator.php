<?php

namespace App\Repositories\Validation;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ConfirmationValidator
{

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

               // new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }

        return $validator->validateWithBag('post');
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [

            'code' => ['required', 'min:6', 'max:906'],
            'token' => ['required', 'string' ,'min:6', 'max:225']

        ];
    }


    /**
     * @return array
     */
    public function messages()
    {

        return [
           
            'code.required' => 'The :attribute is required.',
            'code.min' => 'The :attribute must be at least :min characters',
            'code.max' => 'The :attribute must be at least :max characters',

            'token.required' => 'The :attribute is required.',
            'token.string' => 'The :attribute must be a string.',
            'token.min' => 'The :attribute must be at least :min characters',
            'token.max' => 'The :attribute must be at least :max characters',

         ];
    }
}
