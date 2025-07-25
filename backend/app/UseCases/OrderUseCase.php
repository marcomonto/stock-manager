<?php

namespace App\UseCases;

use App\Dtos\CreateOrderDto;
use App\Dtos\DeleteOrderDto;
use App\Dtos\FindOrderDto;
use App\Dtos\UpdateOrderDto;
use App\Models\Order;
use App\Services\OrderService;
use App\UnitOfWork\UnitOfWork;
use Illuminate\Support\Collection;

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

    /**
     * @throws \Exception
     */
    public function update(UpdateOrderDto $dto): void{
        try{
            $this->unitOfWork->begin();
            $orderToUpdate = $this->orderService->find($dto->orderId);
            $this->orderService->update(
                $orderToUpdate,
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


    /**
     * @throws \Exception
     */
    public function delete(DeleteOrderDto $dto): void {
        try{
            $this->unitOfWork->begin();
            $this->orderService->delete(
                $dto->orderId
            );
            $this->unitOfWork->save();
        }
        catch (\Exception $exception){
            $this->unitOfWork->discard();
            throw $exception;
        }
    }

    public function find(FindOrderDto $dto): Order {
        return $this->orderService->find(
            $dto->orderId
        );
    }

    public function list(): Collection{

    }


}
