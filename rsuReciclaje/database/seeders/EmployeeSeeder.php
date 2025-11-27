<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
        [
            'dni' => 12345678,
            'function_id' => 1,
            'first_name' => 'jose',
            'last_name' => 'rodas',
            'birth_date' => '2000-08-01',
            'email' => 'ajdad@cuenta.com',
            'photo_path' => 'employees/photos/ZHWqtHHeLTTQeOahe0V8CEcO3Y99KbTA7FoCq4OI.png',
            'password' => '$2y$12$R55mSRnQXCgAL7vCmflteOVLG5BKis.UwHyLaWgm2u68qSkwKaQ02',
            'address' => 'las queti importarhadhajhd',
            'active' => 1,
            'phone' => 934489395,
            'license' => null,
            'license_category' => null,
            'pin' => 285148,
            'photo' => null,
            'created_at' => '2025-11-18 22:09:32',
            'updated_at' => '2025-11-18 22:09:32'
        ],
        [
            'dni' => 87654321,
            'function_id' => 1,
            'first_name' => 'renzo',
            'last_name' => 'valencia',
            'birth_date' => '2000-08-12',
            'email' => 'ajdada@prueba.com',
            'photo_path' => 'employees/photos/JbAn3LGZx3eqTgdqtONfLL1zghlOM06dYcO8obcN.png',
            'password' => '$2y$12$MFyGJlFGuF96gYYyuKYiQu83zWWgBwvhiIgdMrckAJgrQ/oKUbEKm',
            'address' => 'las queti importarhadhajhd',
            'active' => 1,
            'phone' => 937376468,
            'license' => null,
            'license_category' => null,
            'pin' => 417601,
            'photo' => null,
            'created_at' => '2025-11-18 22:52:15',
            'updated_at' => '2025-11-18 22:52:15'
        ],
        [
            'dni' => 75342745,
            'function_id' => 2,
            'first_name' => 'angel',
            'last_name' => 'pupuche rodas',
            'birth_date' => '2006-04-07',
            'email' => 'ajdjad@gmail.com',
            'photo_path' => 'employees/photos/BBYDlDrCUsxoaiRXUZtpouNn0dPfCsGbKNpYBTZq.jpg',
            'password' => '$2y$12$cUHkE0Fh68VxeS1itGL5yeqRnsvDh99VhW/JafUP6bmx5i1PJbeoe',
            'address' => 'ahjkdhjadhjhaj akdjadjjd',
            'active' => 1,
            'phone' => 973634666,
            'license' => null,
            'license_category' => null,
            'pin' => 461011,
            'photo' => null,
            'created_at' => '2025-11-18 22:53:21',
            'updated_at' => '2025-11-25 17:24:27'
        ],
        [
            'dni' => 13950478,
            'function_id' => 2,
            'first_name' => 'camila',
            'last_name' => 'rodas',
            'birth_date' => '2000-11-11',
            'email' => 'hdhad@gmail.com',
            'photo_path' => 'employees/photos/hedKaV3Q9yIBDSIV63rTvvqyoYPBMPYdvKw80NEx.jpg',
            'password' => '$2y$12$2dWu7vCQeUzwhZFprxW72evRSzmvsbltckvNp1/GAUvW3KYIPGSdy',
            'address' => 'ahjkdhjadhjhaj akdjadjjd',
            'active' => 1,
            'phone' => 973634666,
            'license' => null,
            'license_category' => null,
            'pin' => 119888,
            'photo' => null,
            'created_at' => '2025-11-24 21:19:22',
            'updated_at' => '2025-11-24 21:19:22'
        ],
        [
            'dni' => 78354931,
            'function_id' => 1,
            'first_name' => 'seclen',
            'last_name' => 'custodio',
            'birth_date' => '1996-05-10',
            'email' => 'adadad@gmail.com',
            'photo_path' => 'employees/photos/uXvhJjpjlTHHxphWmApl0zpWFfRCTx81RdeZzBCs.jpg',
            'password' => '$2y$12$sHueMHuMYSx04TCbJfBtE.c3tv20a11vFthWBNriFAN.qU.tAwceG',
            'address' => 'eluterio ventura huaman 160, urb federico villareal',
            'active' => 1,
            'phone' => 963536378,
            'license' => null,
            'license_category' => null,
            'pin' => 594769,
            'photo' => null,
            'created_at' => '2025-11-24 21:20:54',
            'updated_at' => '2025-11-24 21:20:54'
        ],
        [
            'dni' => 38461834,
            'function_id' => 3,
            'first_name' => 'iris',
            'last_name' => 'reyes',
            'birth_date' => '1999-11-15',
            'email' => 'iufif@gmail.com',
            'photo_path' => 'employees/photos/S31lnnaWjqQqYk2yvKtHDlX6S1t5N4jqh2DWUcYD.jpg',
            'password' => '$2y$12$QCb9firLG8eaJg6XYl5Z8OyxCeMge39XWFGdMd3ZAlMjIKYS4w3sm',
            'address' => 'las queti importarhadhajhd',
            'active' => 1,
            'phone' => 967637163,
            'license' => null,
            'license_category' => null,
            'pin' => 622273,
            'photo' => null,
            'created_at' => '2025-11-24 21:22:56',
            'updated_at' => '2025-11-24 21:22:56'
        ],
        [
            'dni' => 48462845,
            'function_id' => 2,
            'first_name' => 'rodrigo',
            'last_name' => 'pupuche',
            'birth_date' => '1998-07-12',
            'email' => 'jadajd@gmail.com',
            'photo_path' => 'employees/photos/51yI65CMkYa9yNeobbDrSMLX1fnkDJWCp26IeD55.jpg',
            'password' => '$2y$12$IsfRBPJtCL02ZzYLiNNsPeGSTtZ.VF9BTaxZBqgRDSRfoP0D2T.6y',
            'address' => 'eluterio ventura huaman 160, urb federico villareal',
            'active' => 1,
            'phone' => 935465742,
            'license' => null,
            'license_category' => null,
            'pin' => 576048,
            'photo' => null,
            'created_at' => '2025-11-24 21:28:41',
            'updated_at' => '2025-11-24 21:28:41'
        ],
        ];

        foreach ($records as $record) {
            DB::table('employees')->insert($record);
        }
    }
}
