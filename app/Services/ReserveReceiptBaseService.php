<?php

namespace App\Services;

class ReserveReceiptBaseService extends ReserveDocumentService
{
    /**
     * 領収書番号を生成
     * 接頭辞に予約管理を表す「A」を付ける
     * 特にシステムで管理している番号ではないので飾程度のもの

     * TODO フォーマットがこれで良いか一応確認
     *
     * フォーマット: A西暦下2桁 + 会社識別子 + - + 月日
     *
     * @param string $agencyId 会社ID
     * @return string
     */
    public function createReceiptNumber(int $agencyId) : string
    {
        $chars = range('A', 'Z');

        // 次の連番を取得
        $seqNumber = $this->reserveReceiptSequenceService->getNextNumber($agencyId, date('Y-m-d'));

        $ranges = array_chunk(range(1, $seqNumber), 999); // 1000で繰り上がり

        $range = count($ranges) - 1;

        $seq = array_search($seqNumber, $ranges[count($ranges)-1]) + 1;

        $agency = $this->agencyRepository->find($agencyId);

        return sprintf("A%02d%s-%02d%03d%s", date('y'), $agency->identifier, date('m'), $seq, $chars[$range]);
    }

}
