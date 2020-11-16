<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Controller\ValidationController;
use App\Controller\ApiController;

class LoginController extends AbstractController
{
    private $validator;
    private $endpoint;

    function __construct()
    {
        $this->validator = new ValidationController();
        $this->endpoint = new ApiController();
    }

    /**
     * @Route("/", methods={"POST"})
     */
    public function index(Request $request) : Response
    {
        $params = json_decode($request->getContent(), true);

        $resType = 'error';
        $resContent = 'Internal Server Error';
        $resCode = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($this->validator->make($params) == true)
        {
            $this->endpoint->request($params);

            if ($this->endpoint->getStatusCode() == 200 || $this->endpoint->getStatusCode() == 201)
            {
                $jsonData = $this->endpoint->getJsonContent();
                $resType = 'data';
                $resContent = [
                    'email' => $jsonData['email'],
                    'token' => $jsonData['token'],
                    'steamid' => $jsonData['steamid'],
                    'username' => $jsonData['username'],
                    'created_at' => $jsonData['created_at']
                ];
                $resCode = Response::HTTP_OK;
            } 
            else 
            {
                $resContent = 'Invalid Email or Password.';
                $resCode = Response::HTTP_UNAUTHORIZED;
            }
        }
        else
        {
            $resContent = $this->validator->getErrorMessage();
            $resCode = Response::HTTP_BAD_REQUEST;
        }

        return new Response(
            json_encode([ $resType => $resContent ]),
            $resCode,
            ['content-type' => 'application/json']
        );
    }
}