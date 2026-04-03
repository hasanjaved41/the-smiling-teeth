<?php

namespace App\Repositories\Interfaces;

/**
 * Interface RepositoryInterface
 * @author Prajakta Sisale <prajakta.sisale@kissht.com>
 * @since 1.0.0
 */
interface RepositoryInterface
{
    /**
     * @param array $data
     * @return bool
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function create(array $data);

    /**
     * @param string $reference_number
     * @param array $data
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @return bool
     */
    public function update(string $reference_number, array $data);

    /**
     * @param string $reference_number
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     * @return Model|null
     */
    public function findByReference(string $reference_number);

    /**-
     * @param string $reference_number
     * @return Model|null
     * @throws ModelNotFoundException
     * @author Prajakta Sisale <prajakta.sisale@kissht.com>
     */
    public function findOrFailByReference(string $reference_number);
}