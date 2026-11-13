<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtAuth
{
    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->config('jwt');
    }

    public function validateToken()
    {
        $headers = $this->ci->input->request_headers();

        if (!isset($headers['Authorization'])) {
            return [
                'valid' => false,
                'message' => 'Authorization header is missing'
            ];
        }

        $authHeader = $headers['Authorization'];

        if (strpos($authHeader, 'Bearer ') !== 0) {
            return [
                'valid' => false,
                'message' => 'Authorization header format must be: Bearer <token>'
            ];
        }

        $token = substr($authHeader, 7);

        $secretKey = $this->ci->config->item('jwt_secret_key');

        try {
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            return [
                'valid' => true,
                'data' => $decoded->data
            ];
        } catch (ExpiredException $e) {
            return [
                'valid' => false,
                'message' => 'Token has expired, please login again'
            ];
        } catch (SignatureInvalidException $e) {
            return [
                'valid' => false,
                'message' => 'Token signature is invalid'
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Invalid token'
            ];
        }
    }
}