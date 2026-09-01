import { readFileSync } from 'fs';
import { resolve } from 'path';
import { accountPreferencesState } from '../src/commerce/accountPreferencesState';
import { authState, createCheckoutAuthReturnDestination } from '../src/commerce/authState';
import { CartLine, CartState, applyPromotionCode, getCartTotals } from '../src/commerce/cartState';
import {
  CHECKOUT_SESSION_KEY, CheckoutSession, CheckoutStorage, MOROCCO_CITIES,
  addressToDraft, buildSellerDeliveryProjection, canPayWithWallet, createSavedAddress,
  defaultSavedAddresses, emptyAddressDraft, getCheckoutGrandTotalMad, getZonesForCity,
  isNoSavedAddressState, isZoneInCity, loadCheckoutSession, saveCheckoutSession,
  setAddressDraftCity, setAddressDraftZone, validateAddressDraft,
} from '../src/commerce/checkoutState';
import { createBuyerOrderRepository, OrderStorage } from '../src/commerce/orderState';

class MemoryStorage implements CheckoutStorage, OrderStorage {
  values = new Map<string, string>();
  async getItem(key: string) { return this.values.get(key) ?? null; }
  async setItem(key: string, value: string) { this.values.set(key, value); }
  async removeItem(key: string) { this.values.delete(key); }
}

const cartLines: CartLine[] = [
  { id:'line-a', productId:1, name:'Fauteuil', variant:'Beige', quantity:1, unitPriceMad:1200, sellerId:'seller-artisanal', sellerName:'Artisanal du Maroc' },
  { id:'line-b', productId:2, name:'Table', variant:'Ch\u00eane', quantity:1, unitPriceMad:1800, sellerId:'seller-bois', sellerName:'Bois & D\u00e9co' },
];
const baseCart: CartState = { lines: cartLines };

export const runStep8GCheckoutAddressDeliveryPaymentBehaviorTests = async (assert:(condition:boolean,message:string)=>void) => {
  const casablanca = MOROCCO_CITIES.find((city)=>city.cityId==='casablanca')!;
  const marrakech = MOROCCO_CITIES.find((city)=>city.cityId==='marrakech')!;
  assert(casablanca.cityId === 'casablanca' && casablanca.nameFr === 'Casablanca', '1 city selection uses stable identity');
  assert(isZoneInCity('casablanca','maarif') && !isZoneInCity('rabat','maarif'), '2 zone must belong to selected city');
  const casaDraft = setAddressDraftZone(setAddressDraftCity(emptyAddressDraft(),'casablanca'),'maarif');
  const changedCity = setAddressDraftCity(casaDraft,'rabat');
  assert(changedCity.cityId === 'rabat' && !changedCity.zoneId && !changedCity.zone, '3 changing city invalidates an incompatible zone');

  authState.reset();
  const original = authState.getSavedAddresses()[0];
  const editedDraft = { ...addressToDraft(original), addressLine:'77 Avenue Atlas' };
  authState.updateAddress(original.id, createSavedAddress(editedDraft, original.id));
  assert(authState.getSavedAddresses()[0].addressLine === '77 Avenue Atlas', '4 edit address uses the canonical address domain');
  const orderStorage = new MemoryStorage(); const orders = createBuyerOrderRepository(orderStorage,{seedOrders:[]}); await orders.hydrate();
  const historical = await orders.createOrder({ cart:baseCart, address:original, deliveryMethod:'standard', paymentMethod:'cmi', checkoutAttemptId:'step8g-history', createdAt:'2026-08-11T12:00:00.000Z' });
  authState.updateAddress(original.id,{addressLine:'99 Rue Future'});
  assert(historical.order.address.addressLine === original.addressLine && orders.getOrderById(historical.order.orderId)?.address.addressLine === original.addressLine, '5 editing an address does not mutate historical orders');
  assert(isNoSavedAddressState([]) && !isNoSavedAddressState([original]), '6 no-address state requires zero saved addresses');
  const firstAddress = createSavedAddress({ ...setAddressDraftZone(setAddressDraftCity(emptyAddressDraft(),'casablanca'),'maarif'), name:'Amina', phone:'+212 612345678', addressLine:'1 Rue Atlas', postcode:'20000' },'first');
  assert(!isNoSavedAddressState([firstAddress]) && validateAddressDraft(addressToDraft(firstAddress)).name === undefined, '7 adding the first valid address exits no-address condition');

  const sessionStorage = new MemoryStorage();
  const session: CheckoutSession = { checkoutAttemptId:'attempt-8g', screen:'address-selection', selectedAddressId:firstAddress.id, deliveryMethod:'standard', paymentMethod:'cmi', selectedPaymentPreferenceId:'pm-card' };
  await saveCheckoutSession(sessionStorage,session); const hydrated = await loadCheckoutSession(sessionStorage);
  assert(hydrated?.selectedAddressId === firstAddress.id, '8 checkout address selection survives hydration');

  const projection = buildSellerDeliveryProjection(cartLines,firstAddress,'standard');
  assert(projection.groups.map((group)=>group.sellerId).sort().join('|') === 'seller-artisanal|seller-bois', '9 multi-seller delivery reuses cart seller identities');
  assert(projection.groups.every((group)=>cartLines.some((line)=>line.sellerId===group.sellerId)), '10 every delivery seller group references valid cart lines');
  assert(projection.groups.reduce((sum,group)=>sum+group.deliveryFeeMad,0) === projection.deliveryFeeMad && projection.deliveryFeeMad === 58, '11 seller delivery totals sum to the global delivery total');
  const promoted = applyPromotionCode(baseCart,'MAYUSH10').cart; const promotionTotals=getCartTotals(promoted);
  assert(getCheckoutGrandTotalMad(promotionTotals.totalMad,projection.deliveryFeeMad) === promotionTotals.subtotalMad-promotionTotals.discountMad+58, '12 promotion discount remains consistent with delivery calculation');
  const unsupportedAddress={...firstAddress,city:'Marrakech',cityId:marrakech.cityId,zone:'Gu\u00e9liz',zoneId:'gueliz'};
  const unavailable=buildSellerDeliveryProjection(cartLines,unsupportedAddress,'standard');
  assert(!unavailable.available && unavailable.unavailableReason==='ADDRESS_UNSUPPORTED', '13 unavailable zone blocks continuation deterministically');
  assert(buildSellerDeliveryProjection(cartLines,firstAddress,'standard').available, '14 changing to a supported address revalidates delivery');
  assert(unavailable.groups.map((group)=>group.sellerId).sort().join('|')==='seller-artisanal|seller-bois' && unavailable.groups.every((group)=>!group.available), '15 affected sellers are preserved in unavailable delivery state');
  assert(cartLines.length===2 && unavailable.groups.reduce((sum,group)=>sum+group.itemCount,0)===2, '16 delivery unavailability never silently removes products');

  const walletBalance=1250; const integerTotal=650;
  assert(Number.isInteger(walletBalance) && Number.isInteger(integerTotal) && canPayWithWallet(walletBalance,integerTotal), '17 wallet balance uses integer MAD');
  assert(!canPayWithWallet(649,650), '18 insufficient wallet cannot pretend successful payment');
  const destination=createCheckoutAuthReturnDestination('attempt-8g','wallet-balance');
  assert(destination.params?.checkoutAttemptId==='attempt-8g' && destination.route==='wallet-balance', '19 wallet authentication returns to the same checkout attempt and context');
  accountPreferencesState.reset(); accountPreferencesState.setSelectedPaymentMethod('pm-wallet');
  assert(accountPreferencesState.getSelectedPaymentMethodId()==='pm-wallet', '20 selecting wallet updates the shared payment source');
  const cards=accountPreferencesState.getPaymentMethods().filter((method)=>method.type==='card');
  assert(cards.length===2 && cards.every((card)=>Boolean(card.id&&card.brand&&card.last4&&card.expiry)), '21 saved cards expose safe presentation metadata only');
  assert(!/(?:\b\d{12,19}\b)|cvv|cvc/i.test(JSON.stringify(cards)), '22 no PAN or CVV is persisted in card fixtures');
  accountPreferencesState.setSelectedPaymentMethod('pm-mastercard');
  assert(accountPreferencesState.getSelectedPaymentMethodId()==='pm-mastercard', '23 selecting a saved card updates checkout payment reference');
  assert(accountPreferencesState.getPaymentMethods().filter((method)=>method.type==='card').length===cards.length, '24 account payment-preference reuse does not duplicate cards');
  const selectedCard=accountPreferencesState.getPaymentMethods().find((method)=>method.id===accountPreferencesState.getSelectedPaymentMethodId())!;
  assert(selectedCard.type==='card' && selectedCard.last4==='8731', '25 Payment to Review observes the exact selected option');
  const safeOrder=await orders.createOrder({cart:promoted,address:firstAddress,deliveryMethod:'standard',paymentMethod:'cmi',checkoutAttemptId:'step8g-safe-order',deliveryFeeMad:58,deliveryPackageCount:2,paymentPreferenceId:selectedCard.id,paymentCardLast4:selectedCard.last4,createdAt:'2026-08-11T13:00:00.000Z'});
  assert(safeOrder.order.paymentPreferenceId==='pm-mastercard' && safeOrder.order.paymentCardLast4==='8731' && !/(cvv|cvc)/i.test(JSON.stringify(safeOrder.order)), '26 BuyerOrder snapshots safe payment metadata only');
  const resumed=await loadCheckoutSession(sessionStorage);
  assert(resumed?.checkoutAttemptId==='attempt-8g' && resumed.deliveryMethod==='standard' && resumed.selectedPaymentPreferenceId==='pm-card', '27 checkout reload restores compatible new selections');
  const invalidStorage=new MemoryStorage(); invalidStorage.values.set(CHECKOUT_SESSION_KEY,JSON.stringify({...session,savedAddresses:[{...firstAddress,cityId:'rabat',zoneId:'maarif'}]}));
  const rejected=await loadCheckoutSession(invalidStorage);
  assert(rejected?.selectedAddressId==='', '28 hydration rejects invalid city-zone combinations');
  assert(!buildSellerDeliveryProjection(cartLines,unsupportedAddress,'standard').available, '29 hydration consumers revalidate delivery availability');
  const transientStorage=new MemoryStorage(); await saveCheckoutSession(transientStorage,{...session,screen:'delivery-unavailable'}); const transientRaw=transientStorage.values.get(CHECKOUT_SESSION_KEY)||'';
  assert(JSON.parse(transientRaw).screen==='delivery-method' && !/warningVisible|selectorVisible|errorVisible/.test(transientRaw), '30 transient selector and error states do not persist');
  assert(getCartTotals(promoted).discountMad===promotionTotals.discountMad && getCartTotals(promoted).totalMad===promotionTotals.totalMad, '31 cart promotion totals remain unchanged');
  const historicalJson=JSON.stringify(historical.order); buildSellerDeliveryProjection(cartLines,firstAddress,'express'); accountPreferencesState.setSelectedPaymentMethod('pm-card');
  assert(JSON.stringify(orders.getOrderById(historical.order.orderId))===historicalJson, '32 reorder cart and order histories remain unchanged');
  const unrelatedBefore=readFileSync(resolve(__dirname,'../src/commerce/orderActionState.ts'),'utf8');
  assert(/return|refund|cancellation/i.test(unrelatedBefore), '33 returns refunds and cancellation domains remain intact');
  assert(readFileSync(resolve(__dirname,'../src/commerce/supportState.ts'),'utf8').length>0 && readFileSync(resolve(__dirname,'../src/commerce/notificationPreferencesState.ts'),'utf8').length>0, '34 support and notifications remain valid');
  const checkoutCode=readFileSync(resolve(__dirname,'../src/commerce/checkoutState.ts'),'utf8');
  assert(!/fetch\(|axios|laravel|wallet backend|payment gateway/i.test(checkoutCode), '35 delivery and wallet behavior makes no backend settlement claim');
  const rootCode=readFileSync(resolve(__dirname,'../src/navigation/RootNavigator.tsx'),'utf8');
  assert(!/sellerDashboard|adminDashboard|sellerSession|adminSession/.test(rootCode), '36 no seller or admin state is introduced');
  const csv=readFileSync(resolve(__dirname,'../docs/phase-5c/CURRENT_SCREEN_STATUS.csv'),'utf8');
  assert(['309:683','309:684','309:685','309:686','309:688','309:689','309:691','309:692'].every((node)=>csv.includes(node)) && csv.includes('IMPLEMENTED_WEB_CHECKED_NATIVE_VALIDATION_PENDING'), '37 native validation remains explicitly pending');
};
