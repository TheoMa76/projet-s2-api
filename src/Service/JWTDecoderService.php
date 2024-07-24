<?php
namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class JWTDecoderService
{
    private $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    public function decode($token)
    {
        try {
            return $this->jwtEncoder->decode($token);
        } catch (\Exception $e) {
            throw new \RuntimeException('Invalid Token');
        }
    }
}
