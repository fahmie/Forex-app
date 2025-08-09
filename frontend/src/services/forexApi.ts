import axios from "axios";

const API_BASE_URL =
  import.meta.env.VITE_API_URL || "http://localhost:8000/api";

const forexApi = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
});

// Request interceptor
forexApi.interceptors.request.use(
  (config) => {
    console.log(
      "API Request:",
      (config.method || "GET").toUpperCase(),
      config.url
    );
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor
forexApi.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error("API Error:", error.response?.data || error.message);
    return Promise.reject(error);
  }
);

// ===== Types (optional tapi bagus untuk TS) =====
export interface RatesResponse {
  base: string;
  date: string;
  rates: Record<string, number>;
  last_updated?: string;
}

export interface ConvertRequest {
  from: string;
  to: string;
  amount: number;
}

export interface ConvertResponse {
  from: string;
  to: string;
  amount: number;
  rate: number;
  result: number;
}

export const forexService = {
  // Get current exchange rates
  async getCurrentRates(baseCurrency: string = "USD"): Promise<RatesResponse> {
    const { data } = await forexApi.get(`/forex/rates?base=${baseCurrency}`);
    return data;
  },

  // Get rates by specific date (YYYY-MM-DD)
  async getRatesByDate(date: string): Promise<RatesResponse> {
    const { data } = await forexApi.get(`/forex/rates/${date}`);
    return data;
  },

  // Convert currency
  async convertCurrency(
    from: string,
    to: string,
    amount: string | number
  ): Promise<ConvertResponse> {
    const payload: ConvertRequest = { from, to, amount: Number(amount) };
    const { data } = await forexApi.post("/forex/convert", payload);
    return data;
  },

  // Get available currencies
  async getCurrencies(): Promise<Record<string, string>> {
    const { data } = await forexApi.get("/forex/currencies");
    return data;
  },

  // Get historical data
  async getHistoricalData(
    from: string,
    to: string,
    days: number = 30
  ): Promise<unknown> {
    const { data } = await forexApi.get(
      `/forex/history/${from}/${to}?days=${days}`
    );
    return data;
  },
};

export default forexService;
