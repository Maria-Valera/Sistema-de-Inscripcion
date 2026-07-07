<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Persona;

class PersonaSeeder extends Seeder
{
    public function run(): void
    {
        $personas = [
            /* Para alumnos */
            ['primer_nombre' => 'Carlos', 'segundo_nombre' => 'Andrés', 'tercer_nombre' => null, 'primer_apellido' => 'Pérez', 'segundo_apellido' => 'Gómez', 'numero_documento' => '12345678', 'fecha_nacimiento' => '2010-03-12', 'direccion' => 'Av. Bolívar, Caracas', 'email' => 'carlos.perez@example.com', 'status' => true , 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'María', 'segundo_nombre' => 'Andrea', 'tercer_nombre' => null, 'primer_apellido' => 'Rodríguez', 'segundo_apellido' => 'López', 'numero_documento' => '12345679', 'fecha_nacimiento' => '2010-05-22', 'direccion' => 'Calle Sucre, Valencia', 'email' => 'maria.rodriguez@example.com', 'status' => true , 'genero_id' => 2, 'prefijo_id' => 1,'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1,'created_at' => now(), 'updated_at' => now()],
    
            ['primer_nombre' => 'Luis', 'segundo_nombre' => 'Alberto', 'tercer_nombre' => null, 'primer_apellido' => 'Martínez', 'segundo_apellido' => 'Soto', 'numero_documento' => '12345680', 'fecha_nacimiento' => '2010-09-10', 'direccion' => 'Urbanización El Centro, Maracay', 'email' => 'luis.martinez@example.com', 'status' => 1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1,'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Ana', 'segundo_nombre' => 'Cristina', 'tercer_nombre' => null, 'primer_apellido' => 'González', 'segundo_apellido' => 'Rivas', 'numero_documento' => '12345681', 'fecha_nacimiento' => '2010-11-07', 'direccion' => 'Sector La Paz, Barquisimeto', 'email' => 'ana.gonzalez@example.com', 'status' => 1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1,'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'José', 'segundo_nombre' => 'Manuel', 'tercer_nombre' => null, 'primer_apellido' => 'Hernández', 'segundo_apellido' => 'Torres', 'numero_documento' => '12345682', 'fecha_nacimiento' => '2010-01-17', 'direccion' => 'Calle Los Cedros, Mérida', 'email' => 'jose.hernandez@example.com', 'status' => 1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Lucía', 'segundo_nombre' => 'Alejandra', 'tercer_nombre' => null, 'primer_apellido' => 'Ramírez', 'segundo_apellido' => 'Mejías', 'numero_documento' => '12345683', 'fecha_nacimiento' => '2010-07-27', 'direccion' => 'Calle Miranda, Coro', 'email' => 'lucia.ramirez@example.com', 'status' => 1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Miguel', 'segundo_nombre' => 'Ángel', 'tercer_nombre' => null, 'primer_apellido' => 'Morales', 'segundo_apellido' => 'Gil', 'numero_documento' => '12345684', 'fecha_nacimiento' => '2010-02-14', 'direccion' => 'Barrio Unión, San Cristóbal', 'email' => 'miguel.morales@example.com', 'status' => 1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Sofía', 'segundo_nombre' => 'Isabel', 'tercer_nombre' => null, 'primer_apellido' => 'Vargas', 'segundo_apellido' => 'Mendoza', 'numero_documento' => '12345685', 'fecha_nacimiento' => '2010-06-18', 'direccion' => 'Urbanización Las Acacias, Caracas', 'email' => 'sofia.vargas@example.com', 'status' => 1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Daniel', 'segundo_nombre' => 'Enrique', 'tercer_nombre' => null, 'primer_apellido' => 'Castillo', 'segundo_apellido' => 'Rojas', 'numero_documento' => '12345686', 'fecha_nacimiento' => '2010-10-03', 'direccion' => 'Av. Lara, Barquisimeto', 'email' => 'daniel.castillo@example.com', 'status' => 1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre' => 'Paula', 'segundo_nombre' => 'Andrea', 'tercer_nombre' => null, 'primer_apellido' => 'Molina', 'segundo_apellido' => 'Tovar', 'numero_documento' => '12345687', 'fecha_nacimiento' => '2010-04-08', 'direccion' => 'Calle Carabobo, Puerto La Cruz', 'email' => 'paula.molina@example.com', 'status' => 1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()], 

        ];

        $extra = [
            /* Docentes */
           ['primer_nombre'=>'Jorge','segundo_nombre'=>'Luis','tercer_nombre'=>null,'primer_apellido'=>'Salazar','segundo_apellido'=>'Rivas','numero_documento'=>'12345688','fecha_nacimiento'=>'2000-12-04','direccion'=>'Av. Libertador, Caracas','email'=>'jorge.salazar@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Valentina','segundo_nombre'=>'María','tercer_nombre'=>null,'primer_apellido'=>'Figueroa','segundo_apellido'=>'Paredes','numero_documento'=>'12345689','fecha_nacimiento'=>'1996-08-25','direccion'=>'Calle San Juan, La Guaira','email'=>'valentina.figueroa@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Héctor','segundo_nombre'=>'Eduardo','tercer_nombre'=>null,'primer_apellido'=>'Suárez','segundo_apellido'=>'Ramírez','numero_documento'=>'12345690','fecha_nacimiento'=>'1981-09-29','direccion'=>'Sector El Carmen, Maracaibo','email'=>'hector.suarez@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Patricia','segundo_nombre'=>'Elena','tercer_nombre'=>null,'primer_apellido'=>'Miranda','segundo_apellido'=>'Díaz','numero_documento'=>'12345691','fecha_nacimiento'=>'1992-03-16','direccion'=>'La Pastora, Caracas','email'=>'patricia.miranda@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Gabriel','segundo_nombre'=>'José','tercer_nombre'=>null,'primer_apellido'=>'Torrealba','segundo_apellido'=>'García','numero_documento'=>'12345692','fecha_nacimiento'=>'1984-07-11','direccion'=>'Av. Sucre, Valencia','email'=>'gabriel.torrealba@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Tamara','segundo_nombre'=>'Beatriz','tercer_nombre'=>null,'primer_apellido'=>'Landaeta','segundo_apellido'=>'Serrano','numero_documento'=>'12345693','fecha_nacimiento'=>'1989-01-06','direccion'=>'Urb. La Trigaleña, Valencia','email'=>'tamara.landaeta@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            
            ['primer_nombre'=>'Fernando','segundo_nombre'=>'Antonio','tercer_nombre'=>null,'primer_apellido'=>'Paredes','segundo_apellido'=>'Silva','numero_documento'=>'12345694','fecha_nacimiento'=>'1990-12-02','direccion'=>'Calle Vargas, Mérida','email'=>'fernando.paredes@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Natalia','segundo_nombre'=>'Lucía','tercer_nombre'=>null,'primer_apellido'=>'Sánchez','segundo_apellido'=>'Ochoa','numero_documento'=>'12345695','fecha_nacimiento'=>'1993-10-30','direccion'=>'Av. Independentista, Cumaná','email'=>'natalia.sanchez@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Oscar','segundo_nombre'=>'Daniel','tercer_nombre'=>null,'primer_apellido'=>'Arévalo','segundo_apellido'=>'Soto','numero_documento'=>'12345696','fecha_nacimiento'=>'1986-06-19','direccion'=>'Urb. Campo Claro, Caracas','email'=>'oscar.arevalo@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Karen','segundo_nombre'=>'Margarita','tercer_nombre'=>null,'primer_apellido'=>'Oliveros','segundo_apellido'=>'Pinto','numero_documento'=>'12345697','fecha_nacimiento'=>'1991-02-21','direccion'=>'La Candelaria, Caracas','email'=>'karen.oliveros@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            /* Representantes */
            ['primer_nombre'=>'Ricardo','segundo_nombre'=>'Manuel','tercer_nombre'=>null,'primer_apellido'=>'Peña','segundo_apellido'=>'Guzmán','numero_documento'=>'12345698','fecha_nacimiento'=>'1985-11-14','direccion'=>'Av. Bolívar, Maracay','email'=>'representante@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Adriana','segundo_nombre'=>'Soledad','tercer_nombre'=>null,'primer_apellido'=>'Quintero','segundo_apellido'=>'Hernández','numero_documento'=>'12345699','fecha_nacimiento'=>'1994-08-09','direccion'=>'Sector Santa Ana, Mérida','email'=>'adriana.quintero@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Esteban','segundo_nombre'=>'José','tercer_nombre'=>null,'primer_apellido'=>'Barrios','segundo_apellido'=>'Ramos','numero_documento'=>'12345700','fecha_nacimiento'=>'1990-09-14','direccion'=>'Colinas del Tamanaco, Caracas','email'=>'esteban.barrios@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Rebeca','segundo_nombre'=>'Antonella','tercer_nombre'=>null,'primer_apellido'=>'Colmenares','segundo_apellido'=>'González','numero_documento'=>'12345701','fecha_nacimiento'=>'1997-04-03','direccion'=>'Urbanización 23 de Enero, Caracas','email'=>'rebeca.colmenares@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Andrés','segundo_nombre'=>'Felipe','tercer_nombre'=>null,'primer_apellido'=>'Montilla','segundo_apellido'=>'Salas','numero_documento'=>'12345702','fecha_nacimiento'=>'1982-01-01','direccion'=>'Palo Negro, Aragua','email'=>'andres.montilla@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Diana','segundo_nombre'=>'Carolina','tercer_nombre'=>null,'primer_apellido'=>'Cedeño','segundo_apellido'=>'Campos','numero_documento'=>'12345703','fecha_nacimiento'=>'1995-12-28','direccion'=>'Centro, San Carlos','email'=>'diana.cedeno@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Julio','segundo_nombre'=>'César','tercer_nombre'=>null,'primer_apellido'=>'Velásquez','segundo_apellido'=>'Martín','numero_documento'=>'12345704','fecha_nacimiento'=>'1987-03-22','direccion'=>'Calle Páez, Los Teques','email'=>'julio.velasquez@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Elsa','segundo_nombre'=>'Patricia','tercer_nombre'=>null,'primer_apellido'=>'Torres','segundo_apellido'=>'Aguilar','numero_documento'=>'12345705','fecha_nacimiento'=>'1991-09-18','direccion'=>'Casco Central, Maracay','email'=>'elsa.torres@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Rafael','segundo_nombre'=>'Ignacio','tercer_nombre'=>null,'primer_apellido'=>'Guerra','segundo_apellido'=>'Carvajal','numero_documento'=>'12345706','fecha_nacimiento'=>'1986-05-05','direccion'=>'La Urbanización, Barinas','email'=>'rafael.guerra@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Teresa','segundo_nombre'=>'Mariana','tercer_nombre'=>null,'primer_apellido'=>'Bello','segundo_apellido'=>'Serrano','numero_documento'=>'12345707','fecha_nacimiento'=>'1993-10-21','direccion'=>'Av. Libertad, Puerto Ordaz','email'=>'teresa.bello@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            /* Representante */
            ['primer_nombre'=>'Iván','segundo_nombre'=>'Alejandro','tercer_nombre'=>null,'primer_apellido'=>'Ojeda','segundo_apellido'=>'Torres','numero_documento'=>'12345708','fecha_nacimiento'=>'1989-07-17','direccion'=>'Av. Guayana, Ciudad Bolívar','email'=>'ivan.ojeda@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Laura','segundo_nombre'=>'Victoria','tercer_nombre'=>null,'primer_apellido'=>'Acosta','segundo_apellido'=>'Márquez','numero_documento'=>'12345709','fecha_nacimiento'=>'1998-01-13','direccion'=>'Urbanización Centro, Coro','email'=>'laura.acosta@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Mauricio','segundo_nombre'=>'José','tercer_nombre'=>null,'primer_apellido'=>'Gutiérrez','segundo_apellido'=>'Bravo','numero_documento'=>'12345710','fecha_nacimiento'=>'1990-06-01','direccion'=>'Av. Caroní, Puerto Ordaz','email'=>'mauricio.gutierrez@example.com','status'=>1, 'genero_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Daniela','segundo_nombre'=>'Paola','tercer_nombre'=>null,'primer_apellido'=>'Mejía','segundo_apellido'=>'Núñez','numero_documento'=>'12345711','fecha_nacimiento'=>'1994-03-04','direccion'=>'Av. Prolongación, Maturín','email'=>'daniela.mejia@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Edison','segundo_nombre'=>'Ramón','tercer_nombre'=>null,'primer_apellido'=>'León','segundo_apellido'=>'Moreno','numero_documento'=>'12345712','fecha_nacimiento'=>'1987-11-29','direccion'=>'Sector La Esperanza, Guarenas','email'=>'edison.leon@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Marcela','segundo_nombre'=>'Esther','tercer_nombre'=>null,'primer_apellido'=>'Carrillo','segundo_apellido'=>'Valera','numero_documento'=>'12345713','fecha_nacimiento'=>'1996-02-07','direccion'=>'Barrio 5 de Julio, Maracaibo','email'=>'marcela.carrillo@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Víctor','segundo_nombre'=>'Hugo','tercer_nombre'=>null,'primer_apellido'=>'Villalba','segundo_apellido'=>'Castro','numero_documento'=>'12345714','fecha_nacimiento'=>'1988-05-12','direccion'=>'Calle Nueva, Tucupita','email'=>'victor.villalba@example.com','status'=>1, 'genero_id' => 1, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 2, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()],

            ['primer_nombre'=>'Rosa','segundo_nombre'=>'Maribel','tercer_nombre'=>null,'primer_apellido'=>'Ortega','segundo_apellido'=>'Quintana','numero_documento'=>'12345715','fecha_nacimiento'=>'1992-07-01','direccion'=>'Parroquia Catedral, Ciudad Bolívar','email'=>'rosa.ortega@example.com','status'=>1, 'genero_id' => 2, 'prefijo_id' => 1, 'telefono' => '12345678', 'tipo_documento_id' => 1, 'localidad_id' => 1, 'created_at' => now(), 'updated_at' => now()], 
        ]; 

        // unir los arrays para llegar a 40
        $personas = array_merge($personas, $extra);

        foreach ($personas as $p) {
            Persona::create($p);
        }

        $this->command->info('Seeder de personas ejecutado correctamente.');
    }
}
