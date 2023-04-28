<?php

namespace App\Validation;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordResetValidator
{

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Validate the Request and only return the validated fields, otherwise it throws an exception
     * @return object password reset
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

            //new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }

        return $validator->validateWithBag('post');
    }

    public function rules()
    {
        return [

            'username' => ['required', 'min:10'],

        ];
    }

    public function messages()
    {

        return [
            'username.required' => 'The :attribute is required.',
            'username.min' => 'The :attribute must be at least :min characters.',
        ];
    }
}
