<?php

it('redirige la raíz al listado de pacientes', function () {
    $this->get('/')->assertRedirect(route('pacientes.index'));
});
