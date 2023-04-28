<?php
namespace App\Repositories\Validation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PropertyListValidator
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
                            $validator->errors()->getMessages(),
                        ],

                    ])
            );
        }

        return $validator->validateWithBag('post');
    }
   
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'currency' => ['required', 'min:1', 'max:3'],
            'duration' => ['required', 'min:3', 'max:20'],
            'status' => ['required', 'integer', 'gt:0'],
            'amount' => ['required', 'string', 'gt:0'],
            'address' => ['required', 'array'],
            'address.address_name' => ['required', 'string', 'max:160'],
            'address.address_line' => ['required', 'string', 'max:160'],
            'address.locality' => ['required', 'string', 'max:30'],
            'address.sub_locality' => ['required', 'string', 'min:2', 'max:160'],
            'address.latitude' => ['required', 'string'],
            'address.longitude' => ['required', 'string'],
            //'facility' => ['required', 'array'],
            'type' => ['required', 'integer', 'gt:0'],
            'age' => ['required', 'string'],
            'sq_ft' =>['required', 'string'],
            "description" => ['required', 'string'],
           // 'facility.facilities' => ['required', 'array'],
            //'facility.optional' => ['required', 'string'],
              

        ];
    }

    public function messages()
    {

        return [

            'name.required' => 'Name :attribute is Required.',
            'name.string' => 'Name :attribute must be a string.',
            'name.min' => 'Name :attribute must be at least :min.',
            'name.max' => 'Name :attribute must be at least :max.',

            'currency.required' => 'The :attribute is Required.',
            'currency.string' => 'The :attribute must be a string.',
            'currency.min' => 'Currency name must be exactly :min characters long.',
            'currency.max' => 'Currency name must be exactly :max characters long.',

            'status.required' => 'The: attribute rate required.',
            'status.integer' => 'The: attribute must be a valid number',

            'amount.required' => 'The: attribute rate required.',
            'amount.gt0' => 'Rent rate must be greater than or equal zero (0)',


            'duration.required' => 'The :attribute is Required.',
            'duration.string' => 'The :attribute must be a string.',

            'type.required' => 'Category attribute required.',
            'type.integer' => 'The category must be a valid number',

            'age.required' => 'Property age attribute required.',
            'age.string' => 'The :attribute must be a string.',

            'sq_ft.required' => 'Floor area attribute required.',
            'sq_ft.string' => 'Floor area must be a string.',

            'bath_room.required' => 'Bathroom required.',
            'bath_room.string' => 'Bathroom must be a valid string quoted number',
            'bath_room.gt0' => 'Bathroom must be greater than or equal zero (0)',

            'bed_room.required' => 'Bedroom rate required.',
            'bed_room.string' => 'Bedroom must be a valid string quoted number',
            'bed_room.gt0' => 'Bedroom must be greater than or equal zero (0)',

            'address.required' => 'The :attribute is Required',
            'address.array' => 'The address must be an object.',

            'address.latitude.required' => 'The :attribute is Required',
            'address.latitude.integer' => 'The :attribute must be string',


            /*'facility.required' => 'The :attribute is Required',
            'facility.array' => 'The :attribute must be an object.',*/


            'address.line_one.required' => 'The :attribute is Required',
            'address.line_one.string' => 'The :attribute must be string',
            'address.line_one.max' => 'Address line one must be at most :max characters long.',

            'address.line_two.required' => 'The :attribute is Required',
            'address.line_two.string' => 'The :attribute must be string',

           /* 'address.zip_code.required' => 'The :attribute is Required',
            'address.zipcode.string' => 'Zip code must be a string.',
            'address.zipcode.max' => 'Zip code line one must be at most :max characters long.',*/

            'address.country.required' => 'The :attribute is Required.',
            'address.country.string' => 'The :attribute must be string.',
            'address.country.min' => 'Country must be exactly :min characters long.',
            'address.country.max' => 'Country must be exactly :max characters long.',


            'description.required' => 'The :attribute is Required.',
            'description.string' => 'The :attribute must be a string.',



        ];
    }
}
