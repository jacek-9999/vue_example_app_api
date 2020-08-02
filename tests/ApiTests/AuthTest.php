<?php

use App\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    public function testLoginValid()
    {
        $user = new User();
        $user->username= ('test_user');
        $user->password = app('hash')->make('Test1234');
        $user->save();


        $loginPayload = json_encode(['username' => 'test_user', 'password' => 'Test1234']);
        $response = $this
            ->call(
                'POST',
                'login', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $loginPayload
            );
        $r = json_decode($response->getContent(), true);
        $this->assertTrue(isset($r['token']));
        $this->assertTrue(strlen($r['token']) > 40);
        $this->assertEquals('bearer', $r['token_type']);
        $payload = json_decode(substr(base64_decode($r['token']), 27, 168), 1);
        $this->assertEquals('http://localhost/login', $payload['iss']);
        $this->assertEquals(0,$payload['iat'] - time());
        $this->assertEquals(3600,$payload['exp'] - time());//3600sec / 60 min = 1hr
        $this->assertEquals(0,$payload['nbf'] - time());
    }

    public function testLoginInValid()
    {
        $loginPayload = json_encode(['username' => 'wrong_user', 'password' => 'Test1234']);
        $response = $this
            ->call(
                'POST',
                'login', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $loginPayload
            );
        $r = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $r['message']);
    }
}