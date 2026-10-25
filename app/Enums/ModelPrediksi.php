<?php

namespace App\Enums;

/**
 * Model yang menghasilkan prediksi harga. Naive adalah garis dasar yang wajib
 * dikalahkan oleh model ensemble sesuai kriteria penerimaan pada Tabel 3.6.
 */
enum ModelPrediksi: string
{
    case RandomForest = 'random_forest';
    case Xgboost = 'xgboost';
    case Naive = 'naive';

    /**
     * Nama model untuk ditampilkan di antarmuka.
     */
    public function label(): string
    {
        return match ($this) {
            self::RandomForest => 'Random Forest',
            self::Xgboost => 'XGBoost',
            self::Naive => 'Garis dasar Naive',
        };
    }
}
