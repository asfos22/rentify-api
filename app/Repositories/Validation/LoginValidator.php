<?php

namespace App\Repositories\Validation;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginValidator
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

            'email' => ['required', 'min:5'],
            //'username' => ['required', 'min:10'],
            'password' => ['required', 'min:5']
        ];
    }


    /**
     * @return array
     */
    public function messages()
    {

        return [
            //'username.required' => 'The :attribute is required.',
            //'username.min' => 'The :attribute must be at least :min characters',

            'email.required' => 'The :attribute is required.',
            'email.min' => 'The :attribute must be at least :min characters',

            'password.required' => 'The :attribute field is required.',
            'password.min' => 'The :attribute must be at least :min.',
        ];
    }
}
