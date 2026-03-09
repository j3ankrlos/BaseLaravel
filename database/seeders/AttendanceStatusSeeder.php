<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'code' => 'T1', 'description' => 'ASISTENCIA'],
            ['id' => 2, 'code' => 'T2', 'description' => 'TURNO 2'],
            ['id' => 3, 'code' => 'T3', 'description' => 'TURNO 3'],
            ['id' => 4, 'code' => 'L', 'description' => 'LIBRE'],
            ['id' => 5, 'code' => 'V', 'description' => 'VACACIONES'],
            ['id' => 6, 'code' => 'R', 'description' => 'REPOSO'],
            ['id' => 7, 'code' => 'FINR', 'description' => 'FALTA INJUSTIFICADA NO REMUNERADA'],
            ['id' => 8, 'code' => 'FJ', 'description' => 'FALTA JUSTIFICADA'],
            ['id' => 9, 'code' => 'PR', 'description' => 'PERMISO REMUNERADO'],
            ['id' => 10, 'code' => 'DLT', 'description' => 'DIA LIBRE TRABAJADO'],
            ['id' => 11, 'code' => 'LP', 'description' => 'LICENCIA PATERNIDAD'],
            ['id' => 12, 'code' => 'PNR', 'description' => 'PERMISO NO REMUNERADO'],
            ['id' => 13, 'code' => 'FJNR', 'description' => 'FALTA JUSTIFICADA NO REMUNERADA'],
            ['id' => 14, 'code' => 'PV', 'description' => 'PERMISO POR VACACION'],
            ['id' => 15, 'code' => 'C', 'description' => 'DIA COMPENSATORIO'],
            ['id' => 16, 'code' => 'F', 'description' => 'FERIADO NO LABORADO'],
            ['id' => 17, 'code' => 'T4', 'description' => 'TURNO 4'],
            ['id' => 18, 'code' => 'T5', 'description' => 'TURNO 5'],
            ['id' => 19, 'code' => 'X/R', 'description' => 'MEDIO DIA DE REPOSO'],
            ['id' => 20, 'code' => 'X/PNR', 'description' => 'HORAS PERMISO NO REMUNERADO'],
            ['id' => 21, 'code' => 'PCC', 'description' => 'PERMISO CONTRATO COLECTIVO'],
            ['id' => 22, 'code' => 'RET', 'description' => 'RETIRADO'],
            ['id' => 23, 'code' => 'FC', 'description' => 'FINALIZACION DE CONTRATO'],
            ['id' => 24, 'code' => 'HFJ', 'description' => 'HORAS DE FALTA JUSTIFICADA'],
            ['id' => 25, 'code' => 'HFI', 'description' => 'HORAS DE FALTA INJUSTIFICADA'],
            ['id' => 26, 'code' => 'HPL', 'description' => 'HORAS PERMISO LACTANCIA'],
            ['id' => 27, 'code' => 'CA', 'description' => 'CAMBIO DE AREA'],
            ['id' => 28, 'code' => 'L-A', 'description' => 'Trabajos preparatorios o complementarios que deban ejecutarse necesariamente fuera de los límites señalados al trabajo general de la entidad de trabajo.'],
            ['id' => 29, 'code' => 'L-B', 'description' => 'Trabajos que por razones técnicas no pueden interrumpirse a voluntad, o tienen que llevarse a cabo para evitar el deterioro de las materias o de los productos o comprometer el resultado del trabajo.'],
            ['id' => 30, 'code' => 'L-C', 'description' => 'Trabajos indispensables para coordinar la labor de dos equipos que se relevan.'],
            ['id' => 31, 'code' => 'L-D', 'description' => 'Trabajos exigidos por la elaboración de inventarios y balances, vencimientos, liquidaciones, finiquitos y cuentas'],
            ['id' => 32, 'code' => 'L-E', 'description' => 'Trabajos extraordinarios debido a circunstancias particulares, tales como la de terminación o ejecución de una obra urgente, o atender necesidades de la población en ciertas épocas del año.'],
            ['id' => 33, 'code' => 'L-F', 'description' => 'Trabajos especiales y excepcionales como reparaciones, modificaciones o instalaciones de maquinarias nuevas, canalizaciones de agua o gas, líneas o conductores de energía eléctrica o telecomunicaciones.'],
        ];

        foreach ($statuses as $status) {
            AttendanceStatus::updateOrCreate(['id' => $status['id']], $status);
        }
    }
}
