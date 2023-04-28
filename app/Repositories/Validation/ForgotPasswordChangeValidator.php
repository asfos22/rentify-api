<?php

namespace App\Repositories\Validation;

use App\Repositories\Role\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class ForgotPasswordChangeValidator
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
                        "message" => [
                            $validator->errors()->getMessages()
                        ]

                    ])

            );
        }

        return $validator->validateWithBag('post');
    }

    public function rules()
    {

        return [

            'code' => ['required', 'min:6'],
            'token' => ['required', 'min:8'],
            //'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
            'password' => ['required', 'min:8', 'confirmed']
        ];
    }

    public function messages()
    {

        return [

            'code.required' => 'The :attribute is required.',
            'code.min' => 'The : attribute must be at least :min',

           // 'email.required' => 'The :attribute is required.',
           // 'email.regex:/(.+)@(.+)\.(.+)/i' => 'The :attribute is not valid',

            'token.required' => 'The :attribute is required.',
            'token.min' => 'The :attribute must be at least :min.',

            'password.required' => 'The :attribute field is required.',
            'password.min' => 'The :attribute must be at least :min.',
            'password.confirmed' => 'The :attribute confirmation does not match.'
        ];
    }
}
