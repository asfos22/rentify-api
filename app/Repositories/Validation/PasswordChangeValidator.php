<?php

namespace App\Repositories\Validation;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordChangeValidator
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
            'current' => ['required', 'min:8'],
            'new' => ['required', 'min:8'],
        ];
    }

    public function messages()
    {

        return [
            'new.required' => 'The :attribute password is required.',
            'current.required' => 'The :attribute password is required.',

            'current.min' => 'The :attribute must be at least :min characters.',
            'new.min' => 'The :attribute must be at least :min characters.',

        ];
    }
}
