<?php

namespace App\UseCases;

use App\Dtos\CreateOrderDto;
use App\Dtos\DeleteOrderDto;
use App\Dtos\FindOrderDto;
use App\Dtos\ListOrderDto;
use App\Dtos\UpdateOrderDto;
use App\Models\Order;
use App\Services\OrderService;
use App\UnitOfWork\UnitOfWork;
use App\Utils\PaginationOptions;
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
                $dto->name,
                $dto->description,
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
                $dto->name,
                $dto->description,
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

    public function get(FindOrderDto $dto): ?Order {
        return $this->orderService->get(
            $dto->orderId
        );
    }

    public function list(ListOrderDto $dto): Collection{
        if (isset($dto->page) && isset($dto->rowsPerPage)){
            $paginationOptions = new PaginationOptions(
                $dto->page,
                $dto->rowsPerPage,
            );
        }
        return $this->orderService->list(
            name: $dto->name,
            description: $dto->description,
            creationDate: $dto->creationDate,
            paginationOptions: $paginationOptions ?? null,
        );
    }


}
