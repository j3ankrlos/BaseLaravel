<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Area;
use App\Models\AssignedPost;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['id' => 1, 'first' => 'ALEXIS ANTONIO', 'last' => 'ORTIZ', 'dni' => '7913761', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0004', 'nom' => 3, 'pos' => 'SUPERVISOR DE PRODUCCION', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD'],
            ['id' => 2, 'first' => 'PEDRO LUIS', 'last' => 'CANIZALEZ VALERA', 'dni' => '10315331', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0007', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REEMPLAZO EXP'],
            ['id' => 3, 'first' => 'JONHNY RAFAEL', 'last' => 'PERAZA MENDOZA', 'dni' => '11584302', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '30/07/2007', 'ficha' => '0043', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EXP'],
            ['id' => 4, 'first' => 'CARLOS LEOPOLDO', 'last' => 'PEREZ', 'dni' => '11792452', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0005', 'nom' => 3, 'pos' => 'COORDINADOR DE GRANJA', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'COORDINACION'],
            ['id' => 5, 'first' => 'CARLOS ANTONIO', 'last' => 'GARCIA RODRIGUEZ', 'dni' => '12370766', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0008', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EXP'],
            ['id' => 6, 'first' => 'RAFAEL ENRIQUE', 'last' => 'VEGAS PEREZ', 'dni' => '12592397', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0017', 'nom' => 3, 'pos' => 'SUPERVISOR DE PRODUCCION II', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EXP'],
            ['id' => 7, 'first' => 'JEAN CARLOS', 'last' => 'COLMENARES GONZALEZ', 'dni' => '13197256', 'phone' => '4166569827', 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '24/02/2022', 'ficha' => '0442', 'nom' => 3, 'pos' => 'ANALISTA DE GESTION DE LA OPERACION', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'OFICINA'],
            ['id' => 8, 'first' => 'CARLOS ARNOLDO', 'last' => 'MATHEUS PEREZ', 'dni' => '13519985', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '06/06/2003', 'ficha' => '0011', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EST'],
            ['id' => 9, 'first' => 'ERNESTO DAVID', 'last' => 'DUIN ESCALONA', 'dni' => '13679520', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '04/09/2008', 'ficha' => '0049', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'BIOSEGURIDAD'],
            ['id' => 10, 'first' => 'JOSE GREGORIO', 'last' => 'DUDAMEL MERCADO', 'dni' => '13921079', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '02/12/2004', 'ficha' => '0019', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EXP'],
            ['id' => 11, 'first' => 'CARLOS ALBERTO', 'last' => 'RODRIGUEZ MARTINEZ', 'dni' => '14352640', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '05/05/2003', 'ficha' => '0015', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EST'],
            ['id' => 12, 'first' => 'LUIS ABRAHAN', 'last' => 'JIMENEZ MENDOZA', 'dni' => '14592728', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '01/04/2003', 'ficha' => '0009', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EXP'],
            ['id' => 13, 'first' => 'DANIEL JOSE', 'last' => 'BARRIOS MARTINEZ', 'dni' => '14696543', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '04/11/2019', 'ficha' => '0237', 'nom' => 3, 'pos' => 'SUPERVISOR DE PRODUCCION II', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'MATERNIDAD EST'],
            ['id' => 14, 'first' => 'YULIBER RAMON', 'last' => 'VALENZUELA RAGA', 'dni' => '14809442', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '27/06/2008', 'ficha' => '0047', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'BIOSEGURIDAD', 'cc' => 'P210040002', 'post' => 'BIOSEGURIDAD'],
            ['id' => 15, 'first' => 'JOHAN JOSE', 'last' => 'GARCIA GUTIERREZ', 'dni' => '15094018', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '22/09/2021', 'ficha' => '0398', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REEMPLAZO EXP'],
            ['id' => 16, 'first' => 'ROBERT JESUS', 'last' => 'HERRERA JIMENEZ', 'dni' => '15272450', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '28/02/2019', 'ficha' => '0183', 'nom' => 3, 'pos' => 'ENCARGADO DE PRODUCCION', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD NOCHERO'],
            ['id' => 17, 'first' => 'WILMER ALEXANDER', 'last' => 'MENDOZA GONZALEZ', 'dni' => '15272622', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '22/12/2011', 'ficha' => '0066', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EST'],
            ['id' => 18, 'first' => 'ALBERT ZABDIEL', 'last' => 'GARCIA PEREZ', 'dni' => '15427229', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '15/08/2011', 'ficha' => '0064', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EXP'],
            ['id' => 19, 'first' => 'ISBELIA ISABEL', 'last' => 'DIAZ ORTIZ', 'dni' => '16059033', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '26/12/2013', 'ficha' => '0087', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EXP'],
            ['id' => 20, 'first' => 'LEOMAR CRISTOBAL', 'last' => 'MARTINEZ PEREZ', 'dni' => '16059859', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '04/04/2003', 'ficha' => '0010', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'REPRODUCCION EST'],
            ['id' => 21, 'first' => 'YUBETSY JOSEFINA', 'last' => 'LIZARDO', 'dni' => '16187244', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '22/03/2019', 'ficha' => '0191', 'nom' => 1, 'pos' => 'OPERARIO GENERAL', 'area' => 'MATERNIDAD PORCINA', 'cc' => 'P210040005', 'post' => 'MATERNIDAD EST'],
            ['id' => 22, 'first' => 'JORGE LUIS', 'last' => 'CASTANEDA COA', 'dni' => '16239447', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '21/07/2010', 'ficha' => '0060', 'nom' => 3, 'pos' => 'SUPERVISOR DE PRODUCCION II', 'area' => 'REPRODUCCION PORCINA', 'cc' => 'P210040004', 'post' => 'MATERNIDAD EST'],
            ['id' => 23, 'first' => 'CLAUDIA CRISMARY', 'last' => 'SEQUERA MENDOZA', 'dni' => '16278988', 'phone' => null, 'st' => 1, 'mun' => 1, 'par' => 1, 'city' => null, 'addr' => null, 'entry' => '23/06/2022', 'ficha' => '0475', 'nom' => 3, 'pos' => 'SUPERVISOR DE LABORATORIO I', 'area' => 'PRODUCCION DE SEMEN', 'cc' => 'P210040003', 'post' => 'STUD DE MACHOS'],
        ];

        $defaultShift = \App\Models\Shift::where('code', 'T1')->first();

        foreach ($data as $e) {
            $pos = Position::firstOrCreate(['name' => $e['pos']]);
            $area = Area::where('name', $e['area'])->first();
            $post = AssignedPost::firstOrCreate(['name' => $e['post']]);

            Employee::updateOrCreate(
                ['national_id' => $e['dni']],
                [
                    'first_names'      => $e['first'],
                    'last_names'       => $e['last'],
                    'phone_mobile'     => $e['phone'],
                    'state_id'         => $e['st'],
                    'municipality_id'  => $e['mun'],
                    'parish_id'        => $e['par'],
                    'city'             => $e['city'],
                    'address'          => $e['addr'],
                    'entry_date'       => $e['entry'] ? Carbon::createFromFormat('d/m/Y', $e['entry'])->format('Y-m-d') : null,
                    'file_number'      => $e['ficha'],
                    'payroll_type_id'  => $e['nom'],
                    'position_id'      => $pos->id,
                    'area_id'          => $area ? $area->id : null,
                    'cost_center_code' => $e['cc'] ?: ($area ? $area->cost_center : null),
                    'assigned_post_id' => $post->id,
                    'shift_id'         => $defaultShift ? $defaultShift->id : null,
                    'status'           => 'Activo'
                ]
            );
        }
    }
}
