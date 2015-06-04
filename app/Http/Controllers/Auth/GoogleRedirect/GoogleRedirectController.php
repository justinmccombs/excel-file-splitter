<?php namespace PipelineUtilities\Http\Controllers\Auth\GoogleRedirect;
use PipelineUtilities\Http\Controllers\Controller;

/**
 * Created by Justin McCombs.
 * Date: 6/3/15
 * Time: 5:43 PM
 */
class GoogleRedirectController extends Controller
{

    public function getCallback()
    {
        $state = json_decode(\Input::get('state'));
        $url = 'http://';


        if (\Input::has('redirected')) {
            $this->api->client->authenticate(\Input::get('code'));

            $this->authorizationTokenRepository->saveAuthTokenForUser(\Sentry::getUser()->id, $this->api->client->getAccessToken());
            $url = \URL::to($state->route);
        }else {
            if ($state->subdomain)
                $url .= $state->subdomain . '.';

            $url .= 'pipelinecrm.io/google/auth-callback?redirected=true&'.http_build_query(\Input::all());



            return \Redirect::to($url);
        }

        return \Redirect::to($url);
    }

}