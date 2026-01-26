<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class RegistroDatos
{
    #[Assert\NotBlank(message: 'El nombre es obligatorio, no te hagas.')]
    public ?string $nombre = null;

    #[Assert\NotBlank(message: 'El correo es necesario.')]
    #[Assert\Email(message: 'Ese correo no parece válido, checa el @.')]
    public ?string $email = null;

    #[Assert\NotBlank(message: 'Escribe un teléfono.')]
    #[Assert\Regex(
        pattern: '/^[0-9]{10}$/',
        message: 'El teléfono debe ser de exactamente 10 números.'
    )]
    public ?string $telefono = null;

    #[Assert\NotBlank(message: 'La contraseña no puede estar vacía.')]
    public ?string $password = null;
}
