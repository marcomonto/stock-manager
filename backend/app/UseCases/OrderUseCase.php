<?php

namespace App\UseCases;

use App\Dtos\CreateOrderDto;
use App\Services\OrderService;
use App\UnitOfWork\UnitOfWork;

class OrderUseCase
{
    public function __construct(
        protected UnitOfWork $unitOfWork,
        protected OrderService $orderService,
    ){}

    /**
     * @throws \Exception
     */
    public function create(CreateOrderDto $dto): void{
        try{
            $this->unitOfWork->begin();
            $this->orderService->create(
                $dto->orderItems,
                $dto->notes
            );
            $this->unitOfWork->save();
        }
        catch (\Exception $exception){
            $this->unitOfWork->discard();
            throw $exception;
        }
    }

//    public function update(UpdateOrderDto $dto){
//        try{
//            $this->unitOfWork->begin();
//            $this->unitOfWork->save();
//        }
//        catch (\Exception $exception){
//            $this->unitOfWork->discard();
//        }
//    }
}
