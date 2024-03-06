<?php
/**
 * O2TI Payment Subscription.
 *
 * Copyright © 2024 O2TI. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\SubscriptionPayment\Api;

use O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface;

/**
 * Interface para gerenciamento das assinaturas.
 *
 * @api
 */
interface SubscriptionRepositoryInterface
{
    /**
     * Retorna uma lista de todas as assinaturas de pagamento.
     *
     * @return \O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface[]
     */
    public function getList();

    /**
     * Retorna os detalhes de uma assinatura de pagamento específica.
     *
     * @param int $id
     * @return \O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException Se a assinatura não for encontrada.
     */
    public function getById($id);

    /**
     * Exclui uma assinatura de pagamento.
     *
     * @param int $id
     * @return bool Retorna true se a exclusão for bem-sucedida.
     * @throws \Magento\Framework\Exception\NoSuchEntityException Se a assinatura não for encontrada.
     */
    public function delete($id);

    /**
     * Cria ou atualiza uma assinatura de pagamento.
     *
     * @param \O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface $subscription
     * @return \O2TI\SubscriptionPayment\Api\Data\SubscriptionInterface
     */
    public function save(SubscriptionInterface $subscription);
}
