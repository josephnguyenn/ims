<div class="payment-panel">
  <h2>Payment Detail</h2>

  <div class="info-row">
    <span>Subtotal</span>
    <span id="pm-subtotal">0.00 CZK</span>
  </div>

  <div class="info-row">
    <span>Tip (CZK)</span>
    <input type="number" id="pm-tip" step="0.01" value="0">
  </div>

  <div class="info-row total">
    <span>Grand Total (CZK)</span>
    <span id="pm-grand">0.00 CZK</span>
  </div>

  <div class="info-row">
    <span>Rounded (0.5 CZK)</span>
    <span id="pm-rounded">0.00 CZK</span>
  </div>

  <div class="section">
    <label>Payment Currency</label>
    <div class="toggle-group">
      <button id="pm-currency-czk" class="toggle on">CZK</button>
      <button id="pm-currency-eur" class="toggle">EUR</button>
    </div>
  </div>

  <div class="section">
    <label for="pm-tender">Amount Tendered</label>
    <input type="number" id="pm-tender" step="0.01" placeholder="0.00">
  </div>

  <div class="info-row">
    <span>Change Due</span>
    <span id="pm-change">0.00</span>
  </div>

  <div class="section">
    <label>Payment Method</label>
    <div class="toggle-group">
      <button id="pm-method-cash" class="toggle on">Cash</button>
      <button id="pm-method-card" class="toggle">Card</button>
    </div>
  </div>

  <div class="action-buttons">
    <button id="pm-complete" class="btn primary">COMPLETE PAYMENT</button>
    <button id="pm-print"    class="btn outline">PRINT RECEIPT</button>
  </div>
</div>
