<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\Agency;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyRole\AgencyRoleRepository;
use App\Repositories\AgencySequence\AgencySequenceRepository;
use App\Services\AgencyRoleService;
use App\Services\BusinessUserManagerSequenceService;
use App\Services\BusinessUserSequenceService;
use App\Services\DocumentCategoryService;
use App\Services\EstimateSequenceService;
use App\Services\WebEstimateSequenceService;
use App\Services\ReserveSequenceService;
use App\Services\WebReserveSequenceService;
use App\Services\StaffService;
use App\Services\UserCustomItemService;
use App\Services\UserSequenceService;
use App\Services\AgencyConsultationSequenceService;
use App\Services\ReserveInvoiceSequenceService;
use App\Services\ReserveReceiptSequenceService;
use App\Traits\ConstsTrait;
use Carbon\Carbon;
use Hash;
use Illuminate\Support\Arr;
use Vinkla\Hashids\Facades\Hashids;

class AgencyService
{
    use ConstsTrait;

    public function __construct(
        AgencyRepository $agencyRepository,
        AgencyRoleRepository $agencyRoleRepository,
        AgencyRoleService $agencyRoleService,
        AgencySequenceRepository $agencySequenceRepository,
        BusinessUserManagerSequenceService $businessUserManagerSequenceService,
        BusinessUserSequenceService $businessUserSequenceService,
        DocumentCategoryService $documentCategoryService,
        EstimateSequenceService $estimateSequenceService,
        WebEstimateSequenceService $webEstimateSequenceService,
        ReserveSequenceService $reserveSequenceService,
        WebReserveSequenceService $webReserveSequenceService,
        StaffService $staffService,
        UserCustomItemService $userCustomItemService,
        UserSequenceService $userSequenceService,
        AgencyConsultationSequenceService $agencyConsultationSequenceService,
        ReserveInvoiceSequenceService $reserveInvoiceSequenceService,
        ReserveReceiptSequenceService $reserveReceiptSequenceService
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->agencyRoleRepository = $agencyRoleRepository;
        $this->agencyRoleService = $agencyRoleService;
        $this->agencySequenceRepository = $agencySequenceRepository;
        $this->businessUserManagerSequenceService = $businessUserManagerSequenceService;
        $this->businessUserSequenceService = $businessUserSequenceService;
        $this->documentCategoryService = $documentCategoryService;
        $this->estimateSequenceService = $estimateSequenceService;
        $this->webEstimateSequenceService = $webEstimateSequenceService;
        $this->reserveSequenceService = $reserveSequenceService;
        $this->webReserveSequenceService = $webReserveSequenceService;
        $this->staffService = $staffService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userSequenceService = $userSequenceService;
        $this->agencyConsultationSequenceService = $agencyConsultationSequenceService;
        $this->reserveInvoiceSequenceService = $reserveInvoiceSequenceService;
        $this->reserveReceiptSequenceService = $reserveReceiptSequenceService;
    }

    public function find(int $id) : ?Agency
    {
        return $this->agencyRepository->find($id);
    }

    public function paginate($params, int $limit, array $with=[])
    {
        return $this->agencyRepository->paginate(is_array($params) ? $params : [], $limit, $with);
    }

    public function create(array $data) : Agency
    {
        $data['identifier'] = $this->createIdentifier(); // ?????????????????????

        $this->contractDataOrganization($data); // ????????????????????????????????????????????????

        // ????????????????????????????????????????????????????????? definitive ????????????
        $data['definitive'] = Arr::get($data, 'contracts') ? true : false;

        $agency = $this->agencyRepository->create($data);

        // ??????????????????????????????????????????
        $agency->agency_roles()->createMany($this->agencyRoleRepository->getDefaultRoles($agency->id));

        Arr::get($data, 'contracts') && $agency->contracts()->createMany($data['contracts']);


        $this->userSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ???????????????????????????????????????

        $this->businessUserSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ???????????????????????????????????????

        $this->businessUserManagerSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ?????????????????????????????????????????????

        $this->estimateSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ?????????????????????????????????

        $this->webEstimateSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// Web?????????????????????????????????

        $this->reserveSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ?????????????????????????????????

        $this->webReserveSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// Web?????????????????????????????????

        $this->agencyConsultationSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ?????????????????????????????????

        $this->reserveInvoiceSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ????????????????????????????????????

        $this->reserveReceiptSequenceService->initCurrentNumber($agency->id, date('Y-m-d'));// ????????????????????????????????????

        // account, password, person_in_charge_name ???????????????????????????????????????????????????
        $this->staffService->create($agency->id, [
            'account'           => $data['account'],
            'password'          => $data['password'],
            'name'              => $data['person_in_charge_name'],
            'email'             => $data['email'],
            'master'            => true,
            'agency_role_id'    => $this->agencyRoleService->getMasterRoleId($agency->id)
        ]);

        $this->userCustomItemService->setDefaults($agency); // ??????????????????????????????????????????

        $this->documentCategoryService->setDefaults($agency); // ????????????????????????????????????????????????

        return $agency;
    }

    /**
     * ??????
     *
     * @param int $id ??????ID
     * @param array $data ???????????????
     * @return Agency
     * @throws ExclusiveLockException ??????????????????????????????????????????????????????
     */
    public function update(int $id, array $data) : Agency
    {
        $agency = $this->agencyRepository->find($id);
        if ($agency->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        $data = Arr::except($data, ['account', 'identifier']); // ??????????????????????????????????????????

        $this->contractDataOrganization($data); // ????????????????????????????????????????????????

        $agency = $this->agencyRepository->update($id, $data);

        
        if ($password = Arr::get($data, 'master_staff.password')) { // ????????????????????????????????????????????????????????????
            $this->staffService->updateFields(
                $agency->master_staff->id,
                [
                    'password' => Hash::make($password)
                ]
            );
        }
        
        return $agency;
    }

    /**
     * ????????????
     */
    public function updateField(int $id, array $params) : bool
    {
        return $this->agencyRepository->updateField($id, $params);
    }

    /**
     * ??????
     *
     * @param int $id ID
     * @param boolean $isSoftDelete ????????????????????????true???false???????????????
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->agencyRepository->delete($id, $isSoftDelete);
    }

    public function selectSearchCompanyName(string $name, ?int $exclusionId, int $limit): array
    {
        $name = $name ? $name : ''; // ?????????????????????

        return $this->agencyRepository->selectSearchCompanyName($name, $exclusionId, $limit);
    }

    /**
     * ??????ID?????????
     *
     * @param string $account ?????????????????????
     * @return int ??????ID
     */
    public function getIdByAccount($account): ?int
    {
        $result = $this->agencyRepository->findBy(['account' => $account]);
        return $result ? $result->id : null;
    }

    /**
     * ?????????????????????????????????????????????true
     *
     * @return boolean
     */
    public function isAccountExists(string $account): bool
    {
        return $this->agencyRepository->isAccountExists($account);
    }

    /**
     * ??????????????????????????????????????????
     */
    public function getNumberStaffAllowedRange(): array
    {
        return range(1, config('consts.const.NUMBER_STAFF_ALLOWED_MAX'));
    }

    /**
     * ????????????????????????
     *
     * ????????????????????????????????? + ???????????????????????????
     *  AA01 ??? ZZ99
     *  1 ??? 66924 ????????????????????????
     *
     * @return string
     */
    public function createIdentifier() : string
    {
        // ????????????????????????????????????AA???ZZ???
        foreach (range('A', 'Z') as $c1) {
            foreach (range('A', 'Z') as $c2) {
                $chars[] = "{$c1}{$c2}";
            }
        }

        $seqNumber = $this->agencySequenceRepository->getNextNumber();

        $ranges = array_chunk(range(1, $seqNumber), 99); // 100??????????????????

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        return sprintf("%s%02d", $chars[$range], $seq);
    }

    /**
     * agencies????????????????????????????????????????????????????????????
     *
     * @param array $input ???????????????
     * @return void
     */
    private function contractDataOrganization(array &$input) : void
    {
        if (Arr::get($input, 'trial_end_at')) {
            if (preg_match('/(\d{4})\-(\d{1,2})/', $input['trial_end_at'], $m)) {
                //???????????????????????????YYYY-MM????????????????????????????????????????????????
                $input['trial_end_at'] = Carbon::create($m[1], $m[2], 1)->endOfMonth()->toDateString();
            }
        }

        if (Arr::get($input, 'contracts')) {
            foreach ($input['contracts'] as $k => $v) {
                if (preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/', $v['start_at'], $m)) {
                    // YYYY-MM-DD???????????????????????????00:00:00????????????
                    $input['contracts'][$k]['start_at'] = Carbon::create($m[1], $m[2], $m[3], 0, 0, 0);
                }

                if (preg_match('/(\d{4})\-(\d{1,2})\-(\d{1,2})/', $v['end_at'], $m)) {
                    // YYYY-MM-DD???????????????????????????23:59:59????????????
                    $input['contracts'][$k]['end_at'] = Carbon::create($m[1], $m[2], $m[3], 23, 59, 59);
                }
            }
        }
    }
}
