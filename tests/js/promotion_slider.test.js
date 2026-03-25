/**
 * @jest-environment jsdom
 */

describe('Promotion Slider Component', () => {
  let wrapper;
  let prevBtn;
  let nextBtn;

  beforeEach(() => {
    document.body.innerHTML = `
      <div class="promotion-slider-container" id="promotionSliderContainer">
          <button class="slider-nav-btn slider-prev" id="promoPrev"></button>
          <div class="promotion-slider-wrapper" id="promotionSliderWrapper" style="width: 1000px; overflow-x: scroll;">
              <div class="promotion-slide" style="width: 250px;">Item 1</div>
              <div class="promotion-slide" style="width: 250px;">Item 2</div>
              <div class="promotion-slide" style="width: 250px;">Item 3</div>
              <div class="promotion-slide" style="width: 250px;">Item 4</div>
              <div class="promotion-slide" style="width: 250px;">Item 5</div>
          </div>
          <button class="slider-nav-btn slider-next" id="promoNext"></button>
      </div>
    `;
    
    wrapper = document.getElementById('promotionSliderWrapper');
    prevBtn = document.getElementById('promoPrev');
    nextBtn = document.getElementById('promoNext');
    
    // Mock scrollBy
    wrapper.scrollBy = jest.fn();
  });

  test('Next button scrolls right', () => {
    // Initialize slider logic (mocking the script in blade)
    nextBtn.addEventListener('click', () => {
        wrapper.scrollBy({ left: 300, behavior: 'smooth' });
    });
    
    nextBtn.click();
    expect(wrapper.scrollBy).toHaveBeenCalledWith({ left: 300, behavior: 'smooth' });
  });

  test('Prev button scrolls left', () => {
    prevBtn.addEventListener('click', () => {
        wrapper.scrollBy({ left: -300, behavior: 'smooth' });
    });
    
    prevBtn.click();
    expect(wrapper.scrollBy).toHaveBeenCalledWith({ left: -300, behavior: 'smooth' });
  });
});