<?php

namespace App\Repositories\Validation;

use App\Repositories\Role\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;


class RegistrationValidator
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

        $allowedRoles = array(
            Role::HOST,
        );

        return [

            'name' => ['required'],
            'email' => ['required', 'regex:/(.+)@(.+)\.(.+)/i'],
            'phone_number' => ['required', 'min:10', 'max:14'],
            'password' => ['required', 'min:8', 'confirmed'],
            'country' => ['required'],
            'role' => ['required', 'string', Rule::in($allowedRoles)]
        ];
    }

    public function messages()
    {

        return [
            'name.required' => 'The :attribute is required.',

            'email.required' => 'The :attribute is required.',
            'email.regex:/(.+)@(.+)\.(.+)/i' => 'The :attribute is not valid',
            'email.unique' => 'The :attribute has already been taken.',
           
            'phone_number.required' => 'The :attribute is required.',
            'phone_number.unique' => 'The :attribute has already been taken.',

            'country.required' => 'The :attribute is required.',

            'password.required' => 'The :attribute field is required.',
            'password.min' => 'The :attribute must be at least :min.',
            'password.confirmed' => 'The :attribute confirmation does not match.',


            'role.required' => 'The User :attribute is required.',
            'role.in' => 'Sorry resource not permitted'

        ];
    }
}
