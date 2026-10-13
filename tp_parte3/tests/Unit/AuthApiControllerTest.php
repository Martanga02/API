<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../app/controllers/auth-api.controller.php';

// Tests unitarios del login (sin base de datos: UserModel se mockea).
class AuthApiControllerTest extends TestCase
{
    private function userRow(string $password): object
    {
        return (object) [
            'id' => 1,
            'usuario' => 'admin',
            'contrasena' => password_hash($password, PASSWORD_BCRYPT),
            'rol' => 'ADMIN',
        ];
    }

    public function testLoginRejectsAuthorizationThatIsNotBasic(): void
    {
        $model = $this->createMock(UserModel::class);
        $model->expects($this->never())->method('getByUser');

        $controller = new AuthApiController($model);
        $req = (object) ['authorization' => 'Bearer abc123'];
        $res = new Response();

        $this->expectOutputString(json_encode('Autenticación no válida'));
        $controller->login($req, $res);
        $this->assertTrue($res->hasFinished());
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $model = $this->createMock(UserModel::class);
        $model->method('getByUser')->with('admin')->willReturn($this->userRow('admin123'));

        $controller = new AuthApiController($model);
        $req = (object) ['authorization' => 'Basic ' . base64_encode('admin:incorrecta')];
        $res = new Response();

        $this->expectOutputString(json_encode('Usuario o contraseña incorrecta'));
        $controller->login($req, $res);
        $this->assertTrue($res->hasFinished());
    }

    public function testLoginReturnsAValidJwtWithCorrectCredentials(): void
    {
        $model = $this->createMock(UserModel::class);
        $model->method('getByUser')->with('admin')->willReturn($this->userRow('admin123'));

        $controller = new AuthApiController($model);
        $req = (object) ['authorization' => 'Basic ' . base64_encode('admin:admin123')];
        $res = new Response();

        ob_start();
        $controller->login($req, $res);
        $body = json_decode(ob_get_clean());

        $this->assertTrue($res->hasFinished());
        $this->assertNotEmpty($body->token);
        // El token tiene que poder validarse y traer el usuario y rol correctos.
        $payload = validateJWT($body->token);
        $this->assertNotNull($payload);
        $this->assertSame('admin', $payload->usuario);
        $this->assertSame(['ADMIN'], $payload->roles);
    }
}
