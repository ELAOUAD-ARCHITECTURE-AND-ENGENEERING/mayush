/**
 * Mayush Mobile Mapper Type Specifications
 * 
 * Defines type signatures for DTO -> Domain transformations.
 * (Mapper implementations will be added in implementation phase).
 */

import {
  CategoryDto,
  ProductMiniDto,
  ProductDetailDto,
  CartItemDto,
  CartSummaryDto,
  UserDto,
  AddressDto
} from '../api/dto';

import {
  CategoryModel,
  ProductSummaryModel,
  ProductDetailModel,
  CartItemModel,
  CartSummaryModel,
  UserModel,
  UserAddressModel
} from '../domain/models';

export type CategoryMapper = (dto: CategoryDto) => CategoryModel;
export type ProductSummaryMapper = (dto: ProductMiniDto) => ProductSummaryModel;
export type ProductDetailMapper = (dto: ProductDetailDto) => ProductDetailModel;
export type CartItemMapper = (dto: CartItemDto, sellerName: string) => CartItemModel;
export type CartSummaryMapper = (dto: CartSummaryDto) => CartSummaryModel;
export type UserMapper = (dto: UserDto) => UserModel;
export type UserAddressMapper = (dto: AddressDto) => UserAddressModel;
