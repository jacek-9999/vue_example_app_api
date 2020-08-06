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
                'PUT',
                'login', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $loginPayload
            );
        $r = json_decode($response->getContent(), true);
        $this->assertTrue(isset($r['token']));
        $this->assertTrue(strlen($r['token']) > 40);
        $payload = json_decode(substr(base64_decode($r['token']), 27, 168), 1);
        $this->assertEquals('http://localhost/login', $payload['iss']);
        $current = time();
        $this->assertEquals(0,$payload['iat'] - $current);
        $this->assertEquals(3600,$payload['exp'] - $current);//3600sec / 60 min = 1hr
        $this->assertEquals(0,$payload['nbf'] - $current);
    }

    public function testLoginInValid()
    {
        $loginPayload = json_encode(['username' => 'wrong_user', 'password' => 'Test1234']);
        $response = $this
            ->call(
                'PUT',
                'login', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $loginPayload
            );
        $r = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $r['message']);
    }

    public function testRegister()
    {
        $loginPayload = json_encode(
            [
                'username' => 'wrong_user',
                'password' => 'Test1234',
                'password_confirmation' => 'Test1234'
            ]);
        $response = $this
            ->call(
                'POST',
                'register', [], [], [],
                ['CONTENT_TYPE' => 'application/json'],
                $loginPayload
            );
        $r = json_decode($response->getContent(), true);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('failed', $r['result']);
    }
}