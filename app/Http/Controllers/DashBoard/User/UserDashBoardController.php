<?php

namespace App\Http\Controllers\DashBoard\User;

use App\Constants;
use App\Http\Controllers\Controller;

use App\Mail\SignupEmail;
use App\Repositories\Session\SessionInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use function PHPUnit\Framework\isEmpty;

class UserDashBoardController extends Controller
{

    private $account;

    /**
     * @var SessionInterface
     */
    private $sessionManager;


    public function __construct(


        SessionInterface $sessionManager

    )
    {
        $this->sessionManager = $sessionManager;
    }

    public function getDashboard()
    {


        /*$activation = Session::get('not-active');


        $maskEmail = $this->maskEmail->maskEmail($activation['email'], 1, 1);


        if (!is_null($maskEmail) && $activation['code'] == 403) {

            // return view('auth/auth-login');

            return view('auth/auth-confirm')->with('message', $activation['message'] ?? '')->with('data', $maskEmail ?? '');


        }*/


        $this->account = $this->sessionManager->getAccountSession(Constants::SESSION_IS_LOGIN_IN);


        if (empty($this->account)) {

            return redirect()->intended('auth/login');
        }


        /*print ($this->account['data']['name'] ?? '');
        print ($this->account['data']['secret'] ?? '');
        print ($this->account['data']['id'] ?? '');*/

        //return redirect()->intended('auth/login');

        // dd( $account);


        //return $verifyAccount;


        return view('dashboard/property/host/dashboard')->with("account", $this->account['data'] ?? '');

        //return redirect()->intended('auth/login');

    }


    public function getBookingDashboard()
    {

        return view('dashboard/host/booking/dashboard-bookings');

    }


    public function getListDashboard()
    {

        return view('layouts/property/my_list/dashboard-my-listings');

    }

    public function getReviewDashboard()
    {

        return view('dashboard/host/review/dashboard-reviews');

    }


}
