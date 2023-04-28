<?php

namespace App\Validation;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordConfirmValidator
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

            //new JsonResponse($validator->errors()->getMessages(), 422)
            );
        }

        return $validator->validateWithBag('post');
    }

    public function rules()
    {
        return [

            'token' => ['required', 'min:4'],
        ];
    }

    public function messages()
    {

        return [
            'token.required' => 'The :attribute is required.',

            'token.min' => 'The :attribute must be at least :min.',
        ];
    }
}
