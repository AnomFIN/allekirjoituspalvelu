import { useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

export default function AddSignersPage() {
  const navigate = useNavigate();
  const location = useLocation();
  const fileName = location.state?.fileName || sessionStorage.getItem('uploadedDoc') || 'Dokumentti.pdf';

  const [signers, setSigners] = useState([{ name: '', email: '' }]);
  const [message, setMessage] = useState(
    'Hei,\n\nOlen lähettänyt sinulle dokumentin allekirjoitettavaksi. Klikkaa alla olevaa linkkiä allekirjoittaaksesi dokumentin.\n\nYstävällisin terveisin'
  );
  const [errors, setErrors] = useState([]);

  const addSigner = () => {
    setSigners([...signers, { name: '', email: '' }]);
  };

  const removeSigner = (index) => {
    if (signers.length === 1) return;
    setSigners(signers.filter((_, i) => i !== index));
    setErrors(errors.filter((_, i) => i !== index));
  };

  const updateSigner = (index, field, value) => {
    const updated = signers.map((s, i) => (i === index ? { ...s, [field]: value } : s));
    setSigners(updated);
    // Clear error for that field
    const newErrors = [...errors];
    if (newErrors[index]) {
      newErrors[index] = { ...newErrors[index], [field]: '' };
      setErrors(newErrors);
    }
  };

  const validate = () => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const newErrors = signers.map((s) => ({
      name: s.name.trim() === '' ? 'Nimi on pakollinen' : '',
      email: !emailRegex.test(s.email) ? 'Syötä kelvollinen sähköpostiosoite' : '',
    }));
    setErrors(newErrors);
    return newErrors.every((e) => !e.name && !e.email);
  };

  const handleSend = () => {
    if (validate()) {
      navigate('/upload/sent', { state: { fileName, signers } });
    }
  };

  return (
    <div className="max-w-2xl mx-auto space-y-6">
      {/* Page header */}
      <div>
        <button
          onClick={() => navigate('/upload')}
          className="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-3 transition-colors"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          Takaisin
        </button>
        <h1 className="text-2xl font-bold text-slate-900">Lisää allekirjoittajat</h1>
        <p className="text-slate-500 mt-1 text-sm">Lisää henkilöt, joille allekirjoituspyyntö lähetetään.</p>
      </div>

      {/* Steps */}
      <div className="flex items-center gap-0">
        {['Lataa tiedosto', 'Lisää allekirjoittajat', 'Lähetä pyyntö'].map((step, i) => (
          <div key={i} className="flex items-center flex-1 last:flex-none">
            <div className="flex flex-col items-center">
              <div className={`w-7 h-7 rounded-full flex items-center justify-center text-sm font-semibold border-2 ${
                i === 0
                  ? 'bg-blue-100 border-blue-300 text-blue-700'
                  : i === 1
                  ? 'bg-blue-900 border-blue-900 text-white'
                  : 'bg-white border-slate-300 text-slate-400'
              }`}>
                {i === 0 ? (
                  <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 13l4 4L19 7" />
                  </svg>
                ) : i + 1}
              </div>
              <span className={`text-xs mt-1 font-medium whitespace-nowrap ${
                i === 1 ? 'text-blue-900' : 'text-slate-400'
              }`}>
                {step}
              </span>
            </div>
            {i < 2 && (
              <div className={`flex-1 h-0.5 mx-2 mb-4 ${i === 0 ? 'bg-blue-300' : 'bg-slate-200'}`} />
            )}
          </div>
        ))}
      </div>

      {/* Document info */}
      <div className="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <svg className="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <span className="text-sm font-medium text-blue-800 truncate">{fileName}</span>
      </div>

      {/* Signers */}
      <div className="bg-white border border-slate-200 rounded-2xl p-5 space-y-4">
        <h2 className="text-base font-semibold text-slate-900">Allekirjoittajat</h2>

        <div className="space-y-3">
          {signers.map((signer, index) => (
            <div key={index} className="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-3">
              <div className="flex items-center justify-between">
                <span className="text-sm font-medium text-slate-600">
                  Allekirjoittaja {index + 1}
                </span>
                {signers.length > 1 && (
                  <button
                    onClick={() => removeSigner(index)}
                    className="text-slate-400 hover:text-red-500 p-1 rounded-lg hover:bg-red-50 transition-colors"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                )}
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium text-slate-600 mb-1">Nimi</label>
                  <input
                    type="text"
                    placeholder="Etunimi Sukunimi"
                    value={signer.name}
                    onChange={(e) => updateSigner(index, 'name', e.target.value)}
                    className={`w-full px-3 py-2 text-sm border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                      errors[index]?.name ? 'border-red-400' : 'border-slate-300'
                    }`}
                  />
                  {errors[index]?.name && (
                    <p className="text-xs text-red-500 mt-1">{errors[index].name}</p>
                  )}
                </div>
                <div>
                  <label className="block text-xs font-medium text-slate-600 mb-1">Sähköposti</label>
                  <input
                    type="email"
                    placeholder="nimi@esimerkki.fi"
                    value={signer.email}
                    onChange={(e) => updateSigner(index, 'email', e.target.value)}
                    className={`w-full px-3 py-2 text-sm border rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors ${
                      errors[index]?.email ? 'border-red-400' : 'border-slate-300'
                    }`}
                  />
                  {errors[index]?.email && (
                    <p className="text-xs text-red-500 mt-1">{errors[index].email}</p>
                  )}
                </div>
              </div>
            </div>
          ))}
        </div>

        <button
          onClick={addSigner}
          className="flex items-center gap-2 text-sm font-medium text-blue-700 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors border border-blue-200"
        >
          <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
          </svg>
          Lisää allekirjoittaja
        </button>
      </div>

      {/* Message */}
      <div className="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
        <h2 className="text-base font-semibold text-slate-900">Viesti allekirjoittajille</h2>
        <textarea
          rows={5}
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          className="w-full px-3 py-2.5 text-sm border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
        />
        <p className="text-xs text-slate-400">
          Tämä viesti lähetetään sähköpostilla kaikille allekirjoittajille.
        </p>
      </div>

      {/* Submit */}
      <button
        onClick={handleSend}
        className="w-full bg-blue-900 text-white py-3.5 rounded-xl font-semibold hover:bg-blue-800 transition-colors shadow-sm flex items-center justify-center gap-2"
      >
        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
        </svg>
        Lähetä allekirjoituspyyntö
      </button>
    </div>
  );
}
