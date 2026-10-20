<?php

it('protege la raíz y redirige a iniciar sesión a los visitantes', function () {
    $this->get('/')->assertRedirect(route('login'));
});
