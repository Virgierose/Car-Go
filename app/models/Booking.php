<?php

class Booking {

    
    private static array $bookings = [
        ['ref'=>'CARGO-A1B2C3D4', 'customer'=>'Juan dela Cruz',    'vehicle'=>'Toyota Fortuner',   'pickup'=>'Apr 5',  'return'=>'Apr 7',  'amount'=>5000, 'status'=>'active'],
        ['ref'=>'CARGO-E5F6G7H8', 'customer'=>'Maria Santos',      'vehicle'=>'Honda Civic',        'pickup'=>'Mar 20', 'return'=>'Mar 22', 'amount'=>3600, 'status'=>'completed'],
        ['ref'=>'CARGO-I9J0K1L2', 'customer'=>'Pedro Reyes',       'vehicle'=>'Toyota Hi-Ace',      'pickup'=>'Mar 10', 'return'=>'Mar 12', 'amount'=>6400, 'status'=>'completed'],
        ['ref'=>'CARGO-M3N4O5P6', 'customer'=>'Anna Cruz',         'vehicle'=>'Ford Ranger',        'pickup'=>'Feb 14', 'return'=>'Feb 15', 'amount'=>2200, 'status'=>'completed'],
        ['ref'=>'CARGO-Q7R8S9T0', 'customer'=>'Jose Garcia',       'vehicle'=>'Mitsubishi Xpander', 'pickup'=>'Jan 28', 'return'=>'Jan 30', 'amount'=>4000, 'status'=>'cancelled'],
        ['ref'=>'CARGO-C3D4E5F6', 'customer'=>'Linda Reyes',       'vehicle'=>'Toyota Vios',        'pickup'=>'Apr 6',  'return'=>'Apr 8',  'amount'=>3000, 'status'=>'pending'],
    ];

   
    public static function all(): array {
        return self::$bookings;
    }

    
    public static function forClient(): array {
        return self::$bookings;
    }

   
    public static function pending(): array {
        return array_filter(self::$bookings, fn($b) => $b['status'] === 'pending');
    }

   
    public static function generateRef(): string {
        return 'CARGO-' . strtoupper(substr(md5(time()), 0, 8));
    }

    
    public static function stats(): array {
        $all = self::$bookings;
        return [
            'total'     => count($all),
            'active'    => count(array_filter($all, fn($b) => $b['status'] === 'active')),
            'completed' => count(array_filter($all, fn($b) => $b['status'] === 'completed')),
            'pending'   => count(array_filter($all, fn($b) => $b['status'] === 'pending')),
            'revenue'   => array_sum(array_column($all, 'amount')),
        ];
    }
}