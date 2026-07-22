<?php

namespace App\Jobs\Payroll;

use App\Models\Employe;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBulletinsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $mois;
    public int $annee;
    public ?array $employeIds;

    /**
     * Create a new job instance.
     */
    public function __construct(int $mois, int $annee, ?array $employeIds = null)
    {
        $this->mois = $mois;
        $this->annee = $annee;
        $this->employeIds = $employeIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $service = new PayrollCalculationService($this->annee);

        // Récupérer les employés
        $query = Employe::with('paieData')
            ->whereHas('paieData', function ($q) {
                $q->where('actif', true);
            });

        if ($this->employeIds) {
            $query->whereIn('id', $this->employeIds);
        }

        $employes = $query->get();

        $successCount = 0;
        $errorCount = 0;

        foreach ($employes as $employe) {
            try {
                $service->calculateBulletin($employe, $this->mois, $this->annee);
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                Log::error('Erreur génération bulletin job', [
                    'employe_id' => $employe->id,
                    'mois' => $this->mois,
                    'annee' => $this->annee,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Génération bulletins terminée', [
            'mois' => $this->mois,
            'annee' => $this->annee,
            'success' => $successCount,
            'errors' => $errorCount,
            'total' => $employes->count(),
        ]);
    }
}
