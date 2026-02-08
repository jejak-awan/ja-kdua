declare(strict_types=1);

namespace App\Http\Controllers\Api\Isp;

use App\Http\Controllers\Api\V1\BaseApiController;
use App\Services\Isp\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrafficController extends BaseApiController
{
    protected MikrotikService $mikrotikService;

    public function __construct(MikrotikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Get real-time traffic for an interface.
     */
    public function live(Request $request): JsonResponse
    {
        $interface = $request->input('interface');
        if (! is_string($interface)) {
            $interface = 'ether1-gateway';
        }
        
        $data = $this->mikrotikService->getInterfaceTraffic($interface);

        if ($data === null) {
            return $this->error('Failed to fetch traffic data', 500, [
                'rx' => 0,
                'tx' => 0
            ]);
        }

        return $this->success($data);
    }
}
